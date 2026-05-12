// Global type definitions for the application

// Entities
export type EntityType = 'client' | 'supplier' | 'both';

export interface CountryOption {
  id: number;
  name: string;
  code: string;
  active?: boolean;
}

export interface Entity {
  id?: number;
  type: EntityType;
  nif: string;
  name: string;
  address: string;
  postal_code: string;
  city: string;
  country_id: number | null;
  phone?: string | null;
  mobile?: string | null;
  website?: string | null;
  email?: string | null;
  rgpd_consent?: boolean;
  observations?: string | null;
  active?: boolean;
  created_at?: string;
  updated_at?: string;
}

// API Responses
export interface PaginatedResponse<T> {
  data?: T[];
  meta?: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}

// Other types can be added here as needed
