import { apiGet, apiPost, apiUrl, type ApiResponse } from './api';
import type { User } from '../types/domain';

export type AuthPayload = {
  user: User;
};

export function me(): Promise<ApiResponse<AuthPayload>> {
  return apiGet<AuthPayload>('auth/me');
}

export function login(payload: { login: string; password: string; remember: boolean }): Promise<ApiResponse<AuthPayload>> {
  return apiPost<AuthPayload>('auth/login', payload);
}

export function register(payload: { username: string; email: string; password: string; remember: boolean }): Promise<ApiResponse<AuthPayload>> {
  return apiPost<AuthPayload>('auth/register', payload);
}

export function forgotPassword(payload: { email: string }): Promise<ApiResponse<{ message: string }>> {
  return apiPost<{ message: string }>('auth/password/forgot', payload);
}

export function resetPassword(payload: { token: string; password: string }): Promise<ApiResponse<{ message: string }>> {
  return apiPost<{ message: string }>('auth/password/reset', payload);
}

export function logout(): Promise<ApiResponse<Record<string, never>>> {
  return apiPost<Record<string, never>>('auth/logout', {});
}

export function googleLoginUrl(): string {
  return apiUrl('auth/google/start');
}
