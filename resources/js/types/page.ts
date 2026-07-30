export type AuthUser = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    profile_photo_url?: string;
    profile_photo_path?: string | null;
    two_factor_enabled?: boolean;
    current_team_id?: number | null;
    current_team?: {
        id: number;
        name: string;
    } | null;
    all_teams?: Array<{
        id: number;
        name: string;
    }>;
};

export type AppPageProps = {
    auth: {
        user: AuthUser | null;
    };
    jetstream: {
        canCreateTeams?: boolean;
        canManageTwoFactorAuthentication?: boolean;
        canUpdatePassword?: boolean;
        canUpdateProfileInformation?: boolean;
        flash?: {
            banner?: string;
            bannerStyle?: string;
            token?: string;
        };
        hasAccountDeletionFeatures?: boolean;
        hasApiFeatures?: boolean;
        hasTeamFeatures?: boolean;
        hasTermsAndPrivacyPolicyFeature?: boolean;
        managesProfilePhotos?: boolean;
        hasEmailVerification?: boolean;
    };
    can?: Record<string, boolean>;
    flash?: {
        success?: string | null;
        error?: string | null;
        warning?: string | null;
        info?: string | null;
    };
    ziggy?: Record<string, unknown>;
};
