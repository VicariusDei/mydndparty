export type Campaign = {
  id: number;
  name: string;
  notes: string | null;
  is_active: number | boolean;
  created_at?: string;
  updated_at?: string | null;
};

export type PartyMember = {
  id: number;
  campaign_id: number;
  user_id: number;
  player_name: string;
  character_name: string;
  class_name: string | null;
  ancestry_name: string | null;
  motto: string | null;
  initiative_bonus: number;
  created_at?: string;
  updated_at?: string | null;
};
