export type User = {
  id: number;
  username: string;
  email: string;
  display_name: string;
  avatar_url: string | null;
  is_admin: boolean;
};

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

export type PlayerNoteRecipient = {
  note_id: number;
  recipient_user_id: number | null;
  recipient_party_member_id: number | null;
  display_name?: string | null;
  username?: string | null;
  character_name?: string | null;
  player_name?: string | null;
};

export type PlayerNote = {
  id: number;
  campaign_id: number;
  session_id: number | null;
  author_user_id: number | null;
  author_party_member_id: number | null;
  author_label: string | null;
  origin_channel: string;
  note_type: string;
  title: string | null;
  content: string;
  share_scope: string;
  status: string;
  master_flag: string;
  corrected_by_user_id: number | null;
  corrected_at: string | null;
  converted_target_type: string;
  converted_target_id: number | null;
  created_at: string;
  updated_at: string | null;
  author_display_name?: string | null;
  author_username?: string | null;
  author_character_name?: string | null;
  author_player_name?: string | null;
  corrected_by_display_name?: string | null;
  corrected_by_username?: string | null;
  recipients?: PlayerNoteRecipient[];
};

export type InventoryItem = {
  id: number;
  campaign_id: number;
  owner_party_member_id: number | null;
  name: string;
  category: string | null;
  quantity: number;
  value_gold: number | string;
  is_identified: number | boolean;
  notes: string | null;
  owner_character_name?: string | null;
  owner_player_name?: string | null;
  created_at?: string;
  updated_at?: string | null;
};

export type WalletRow = {
  id: number;
  campaign_id: number;
  party_member_id: number | null;
  coin_type_id: number;
  quantity: number;
  deposit_quantity: number;
  code: string;
  name: string;
  gold_value: number | string;
  weight_value: number | string;
  owner_character_name?: string | null;
};

export type Encounter = {
  id: number;
  campaign_id: number;
  name: string;
  current_round: number;
  is_active: number | boolean;
  combatants_count?: number;
  created_at?: string;
  updated_at?: string | null;
};

export type CombatEffect = {
  id: number;
  combatant_id: number;
  name: string;
  remaining_rounds: number;
  is_permanent: number | boolean;
  created_at?: string;
};

export type Combatant = {
  id: number;
  encounter_id: number;
  party_member_id: number | null;
  name: string;
  type: string;
  initiative: number;
  initiative_bonus: number;
  is_slow: number | boolean;
  has_acted: number | boolean;
  sort_order: number;
  character_name?: string | null;
  player_name?: string | null;
  class_name?: string | null;
  ancestry_name?: string | null;
  effects?: CombatEffect[];
};

export type DashboardStats = {
  campaigns: number;
  party_members: number;
  inventory_items: number;
  wallet_rows: number;
  encounters: number;
  combatants: number;
  messages: number;
  friend_requests: number;
};

export type DashboardSummary = {
  campaign: Campaign | null;
  stats: DashboardStats;
  party_members: PartyMember[];
  recent_inventory: InventoryItem[];
  wallet: WalletRow[];
  active_encounter: Encounter | null;
  combatants: Combatant[];
  messages: unknown[];
  friend_requests: unknown[];
};
