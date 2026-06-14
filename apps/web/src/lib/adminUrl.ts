import { apiBase } from '../api';

export function adminPanelUrl(): string {
  return `${apiBase()}/admin`;
}
