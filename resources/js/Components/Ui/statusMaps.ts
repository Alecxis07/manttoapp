export type StatusMeta = {
    label: string;
    color: string;
};

/** Common status → chip color map used across Mantto modules. */
export const orderStatusMap: Record<string, StatusMeta> = {
    received: { label: 'Recibida', color: 'info' },
    diagnosing: { label: 'En diagnóstico', color: 'warning' },
    pending_approval: { label: 'Pendiente de aprobación', color: 'warning' },
    approved: { label: 'Aprobada', color: 'primary' },
    in_progress: { label: 'En progreso', color: 'primary' },
    completed: { label: 'Terminada', color: 'success' },
    delivered: { label: 'Entregada', color: 'success' },
    cancelled: { label: 'Cancelada', color: 'error' },
};

export const quotationStatusMap: Record<string, StatusMeta> = {
    draft: { label: 'Borrador', color: 'default' },
    sent: { label: 'Enviada', color: 'info' },
    accepted: { label: 'Aceptada', color: 'success' },
    rejected: { label: 'Rechazada', color: 'error' },
    expired: { label: 'Vencida', color: 'warning' },
    cancelled: { label: 'Cancelada', color: 'error' },
};

export const billingStatusMap: Record<string, StatusMeta> = {
    draft: { label: 'Borrador', color: 'default' },
    pending_review: { label: 'En revisión', color: 'warning' },
    incomplete: { label: 'Incompleta', color: 'warning' },
    approved: { label: 'Aprobada', color: 'primary' },
    processed: { label: 'Procesada', color: 'success' },
    rejected: { label: 'Rechazada', color: 'error' },
    cancelled: { label: 'Cancelada', color: 'error' },
};

export const customerStatusMap: Record<string, StatusMeta> = {
    active: { label: 'Activo', color: 'success' },
    inactive: { label: 'Inactivo', color: 'default' },
};

export const vehicleStatusMap: Record<string, StatusMeta> = {
    active: { label: 'Activa', color: 'success' },
    in_service: { label: 'En servicio', color: 'info' },
    inactive: { label: 'Inactiva', color: 'default' },
    baja: { label: 'Baja', color: 'error' },
};

export const userStatusMap: Record<string, StatusMeta> = {
    active: { label: 'Activo', color: 'success' },
    inactive: { label: 'Inactivo', color: 'default' },
};

export const activeFlagMap: Record<string, StatusMeta> = {
    active: { label: 'Activo', color: 'success' },
    inactive: { label: 'Inactivo', color: 'default' },
};

export const activeFlagFeminineMap: Record<string, StatusMeta> = {
    active: { label: 'Activa', color: 'success' },
    inactive: { label: 'Inactiva', color: 'default' },
};
