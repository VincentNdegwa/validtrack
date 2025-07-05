export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  company_id?: number;
  role?: string;
  phone?: string;
  address?: string;
  website?: string;
  location?: string;
  logo?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  slug: string;
  roles?: Role[];
}

// Add Role and Permission interfaces
export interface Role {
  id: number;
  name: string;
  display_name?: string;
  description?: string;
  company_id: number;
  created_at: string;
  updated_at: string;
  slug: string;
  permissions?: Permission[];
  users?: User[];
  users_count?: number;
  permissions_count?: number;
}

export interface Permission {
  id: number;
  name: string;
  display_name?: string;
  description?: string;
  company_id: number | null;
  created_at: string;
  updated_at: string;
  slug: string;
  roles?: Role[];
  roles_count?: number;
}

export interface Company {
  id: number;
  name: string;
  email: string;
  phone?: string;
  address?: string;
  website?: string;
  logo?: string;
  location?: string;
  description?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  slug: string;
  users_count?: number;
  users?: User[];
}

export interface SubjectType {
  id: number;
  name: string;
  company_id: number;
  created_at: string;
  updated_at: string;
  company?: Company;
  slug: string;
}

export interface Subject {
  id: number;
  name: string;
  company_id: number;
  subject_type_id?: number;
  user_id?: number;
  email?: string;
  phone?: string;
  address?: string;
  category?: string;
  notes?: string;
  status: number;
  created_at: string;
  updated_at: string;
  company?: Company;
  subject_type?: SubjectType;
  user?: User;
  documents?: Document[];
  slug: string;
}

export interface DocumentType {
  id: number;
  name: string;
  company_id: number;
  description?: string;
  icon?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  company?: Company;
  slug: string;
}

export interface Document {
  id: number;
  subject_id: number;
  document_type_id?: number;
  file_url: string;
  issue_date: string;
  expiry_date?: string;
  status: number;
  uploaded_by?: number;
  notes?: string;
  company_id: number;
  created_at: string;
  updated_at: string;
  subject?: Subject;
  document_type?: DocumentType;
  uploader?: User;
  company?: Company;
  slug: string;
}

export interface ActivityLog {
  id: number;
  user_id: number;
  company_id: number;
  action_type: string;
  target_type: string;
  target_id: number;
  payload?: any;
  created_at: string;
  updated_at: string;
  user?: User;
  company?: Company;
  slug: string;
}
