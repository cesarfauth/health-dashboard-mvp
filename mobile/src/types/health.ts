// Mirrors the Laravel API contract (App\Http\Resources\*).

export type BiomarkerStatus = 'normal' | 'attention' | 'alert';

export type BiomarkerKey = 'sleep_hours' | 'glucose_level' | 'hrv';

export interface Biomarker {
  value: number;
  unit: string;
  label: string;
  status: BiomarkerStatus;
  status_label: string;
  color: string;
}

export interface Recommendation {
  title: string;
  detail: string;
  category: string;
}

export interface AiRecommendation {
  id: number;
  type: 'snapshot' | 'trend';
  summary: string;
  recommendations: Recommendation[];
  disclaimer: string;
  source: 'claude' | 'fallback';
  model: string | null;
  generated_at: string;
}

export interface HealthRecord {
  id: number;
  user_id: number;
  recorded_at: string;
  biomarkers: Record<BiomarkerKey, Biomarker>;
  recommendation: AiRecommendation | null;
}

export interface BiomarkerInput {
  sleep_hours: number;
  glucose_level: number;
  hrv: number;
}

/** Shape of a Laravel 422 validation response. */
export interface ValidationErrorResponse {
  message: string;
  errors: Record<string, string[]>;
}

// --- Trend analysis (differential) ---

export type TrendDirection = 'up' | 'down' | 'stable';

export interface TrendMetric {
  label: string;
  unit: string;
  first: number;
  latest: number;
  average: number;
  min: number;
  max: number;
  change_pct: number;
  direction: TrendDirection;
}

export interface TrendInsufficient {
  type: 'trend';
  status: 'insufficient_data';
  records_analyzed: number;
  required: number;
  message: string;
}

export interface TrendOk {
  type: 'trend';
  status: 'ok';
  records_analyzed: number;
  period: { from: string; to: string; days_span: number };
  metrics: Record<BiomarkerKey, TrendMetric>;
  recommendation: AiRecommendation;
}

export type TrendResult = TrendInsufficient | TrendOk;
