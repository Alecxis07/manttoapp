<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/Ui/ActionSection.vue';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';

const props = defineProps<{
    requiresConfirmation?: boolean;
}>();

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref<string | null>(null);
const setupKey = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => !enabling.value && !!(page.props.auth as { user?: { two_factor_enabled?: boolean } }).user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

function enableTwoFactorAuthentication(): void {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = !!props.requiresConfirmation;
        },
    });
}

function showQrCode(): Promise<void> {
    return axios.get(route('two-factor.qr-code')).then((response) => {
        qrCode.value = response.data.svg;
    });
}

function showSetupKey(): Promise<void> {
    return axios.get(route('two-factor.secret-key')).then((response) => {
        setupKey.value = response.data.secretKey;
    });
}

function showRecoveryCodes(): Promise<void> {
    return axios.get(route('two-factor.recovery-codes')).then((response) => {
        recoveryCodes.value = response.data;
    });
}

function confirmTwoFactorAuthentication(): void {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: 'confirmTwoFactorAuthentication',
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
}

function regenerateRecoveryCodes(): void {
    axios.post(route('two-factor.recovery-codes')).then(() => showRecoveryCodes());
}

function disableTwoFactorAuthentication(): void {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
}
</script>

<template>
    <ActionSection
        title="Two Factor Authentication"
        description="Add additional security to your account using two factor authentication."
    >
        <template #content>
            <h3 class="text-subtitle-1 font-weight-bold mb-2">
                <template v-if="twoFactorEnabled && !confirming">
                    You have enabled two factor authentication.
                </template>
                <template v-else-if="twoFactorEnabled && confirming">
                    Finish enabling two factor authentication.
                </template>
                <template v-else>
                    You have not enabled two factor authentication.
                </template>
            </h3>

            <p class="text-body-2 text-medium-emphasis mb-4">
                When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
            </p>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <p class="text-body-2 text-medium-emphasis mb-3">
                        <template v-if="confirming">
                            <strong>To finish enabling two factor authentication,</strong>
                            scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.
                        </template>
                        <template v-else>
                            Two factor authentication is now enabled. Scan the following QR code using your phone's authenticator application or enter the setup key.
                        </template>
                    </p>

                    <div class="d-inline-block pa-2 bg-white rounded mb-3" v-html="qrCode" />

                    <p v-if="setupKey" class="text-body-2 mb-3">
                        <strong>Setup Key:</strong>
                        <span v-html="setupKey" />
                    </p>

                    <v-text-field
                        v-if="confirming"
                        v-model="confirmationForm.code"
                        label="Code"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        autofocus
                        class="mb-2"
                        style="max-width: 280px"
                        :error-messages="confirmationForm.errors.code"
                        @keyup.enter="confirmTwoFactorAuthentication"
                    />
                </div>

                <div v-if="recoveryCodes.length > 0 && !confirming" class="mb-4">
                    <p class="text-body-2 font-weight-medium mb-2">
                        Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.
                    </p>
                    <v-sheet border rounded="lg" class="pa-4 font-mono text-body-2">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </v-sheet>
                </div>
            </div>

            <div class="d-flex flex-wrap ga-2 mt-2">
                <template v-if="!twoFactorEnabled">
                    <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
                        <v-btn
                            color="primary"
                            variant="flat"
                            type="button"
                            :loading="enabling"
                            :disabled="enabling"
                        >
                            Enable
                        </v-btn>
                    </ConfirmsPassword>
                </template>

                <template v-else>
                    <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
                        <v-btn
                            v-if="confirming"
                            color="primary"
                            variant="flat"
                            type="button"
                            :loading="enabling || confirmationForm.processing"
                            :disabled="enabling || confirmationForm.processing"
                        >
                            Confirm
                        </v-btn>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
                        <v-btn
                            v-if="recoveryCodes.length > 0 && !confirming"
                            variant="outlined"
                            type="button"
                        >
                            Regenerate Recovery Codes
                        </v-btn>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="showRecoveryCodes">
                        <v-btn
                            v-if="recoveryCodes.length === 0 && !confirming"
                            variant="outlined"
                            type="button"
                        >
                            Show Recovery Codes
                        </v-btn>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <v-btn
                            v-if="confirming"
                            variant="text"
                            type="button"
                            :loading="disabling"
                            :disabled="disabling"
                        >
                            Cancel
                        </v-btn>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <v-btn
                            v-if="!confirming"
                            color="error"
                            variant="flat"
                            type="button"
                            :loading="disabling"
                            :disabled="disabling"
                        >
                            Disable
                        </v-btn>
                    </ConfirmsPassword>
                </template>
            </div>
        </template>
    </ActionSection>
</template>
