export interface MediaFolder {
  id: number;
  name: string;
  parent_id: number | null;
  created_at: string;
  updated_at: string;
  parent?: MediaFolder;
}

export interface MediaFile {
  id: number;
  name: string;
  file_name: string;
  mime_type: string;
  size: number;
  folder_id: number | null;
  uploaded_by: number | null;
  url: string;
  thumbnail_url: string | null;
  file_type: 'image' | 'video' | 'audio' | 'document' | 'other';
  human_size: string;
  created_at: string;
  updated_at: string;
  folder?: MediaFolder;
  uploader?: {
    id: number;
    name: string;
  };
}

export interface MediaFilesResponse {
  files: {
    data: MediaFile[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  folders: MediaFolder[];
  currentFolder: MediaFolder | null;
}

export interface MediaFilters {
  folder_id?: number | null;
  search?: string;
  type?: 'image' | 'document' | 'video' | 'audio';
}
