export interface NavItem {
    key: string;
    label: string;
    href: string;
    active: boolean;
    icon: string;
}

export interface NavSectionData {
    label: string;
    items: NavItem[];
}
