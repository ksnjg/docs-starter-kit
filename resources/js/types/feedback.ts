export interface FeedbackResponse {
  id: number;
  page_id: number;
  feedback_form_id: number | null;
  is_helpful: boolean;
  form_data: Record<string, unknown> | null;
  ip_address: string;
  created_at: string;
  page?: { id: number; title: string; slug: string };
  feedback_form?: { id: number; name: string };
}

export interface PageOption {
  id: number;
  title: string;
  slug: string;
}

export interface PageStat {
  page_id: number;
  page_title: string;
  page_slug: string;
  total: number;
  helpful_count: number;
  helpfulness_score: number;
}

export interface FeedbackStats {
  total: number;
  helpful: number;
  notHelpful: number;
  helpfulPercentage: number;
  todayCount: number;
}

export interface FeedbackForm {
  id: number;
  name: string;
  trigger_type: 'positive' | 'negative' | 'always';
  fields: FeedbackFormField[];
  is_active: boolean;
  responses_count?: number;
}

export interface FeedbackFormField {
  type: 'text' | 'textarea' | 'radio' | 'checkbox' | 'rating' | 'email';
  label: string;
  required: boolean;
  options?: string[];
}
