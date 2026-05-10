import { Platform } from 'react-native';
import * as SecureStore from 'expo-secure-store';

const AUTH_TOKEN_KEY = 'opoldocs_auth_token';
const AUTH_USER_KEY = 'opoldocs_auth_user';

type PersistedAuthSession = {
  token: string;
  user: any | null;
};

function canUseWebStorage(): boolean {
  return Platform.OS === 'web' && typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';
}

async function readItem(key: string): Promise<string | null> {
  if (canUseWebStorage()) {
    return window.localStorage.getItem(key);
  }

  return SecureStore.getItemAsync(key);
}

async function writeItem(key: string, value: string): Promise<void> {
  if (canUseWebStorage()) {
    window.localStorage.setItem(key, value);
    return;
  }

  await SecureStore.setItemAsync(key, value);
}

async function deleteItem(key: string): Promise<void> {
  if (canUseWebStorage()) {
    window.localStorage.removeItem(key);
    return;
  }

  await SecureStore.deleteItemAsync(key);
}

export function setGlobalAuthSession(token?: string, user?: any): void {
  (globalThis as any).apiToken = token;
  (globalThis as any).currentUser = user;
}

export async function persistAuthSession(token: string, user: any): Promise<void> {
  setGlobalAuthSession(token, user);
  await Promise.all([
    writeItem(AUTH_TOKEN_KEY, token),
    writeItem(AUTH_USER_KEY, JSON.stringify(user ?? null)),
  ]);
}

export async function persistCurrentUser(user: any): Promise<void> {
  const token = (globalThis as any)?.apiToken as string | undefined;
  setGlobalAuthSession(token, user);

  if (user == null) {
    await deleteItem(AUTH_USER_KEY);
    return;
  }

  await writeItem(AUTH_USER_KEY, JSON.stringify(user));
}

export async function hydrateAuthSession(): Promise<PersistedAuthSession | null> {
  const [token, rawUser] = await Promise.all([
    readItem(AUTH_TOKEN_KEY),
    readItem(AUTH_USER_KEY),
  ]);

  if (!token) {
    setGlobalAuthSession(undefined, undefined);
    if (rawUser) {
      await deleteItem(AUTH_USER_KEY);
    }
    return null;
  }

  let user: any | null = null;
  if (rawUser) {
    try {
      user = JSON.parse(rawUser);
    } catch {
      user = null;
    }
  }

  setGlobalAuthSession(token, user);
  return { token, user };
}

export async function clearPersistedAuthSession(): Promise<void> {
  setGlobalAuthSession(undefined, undefined);
  await Promise.all([
    deleteItem(AUTH_TOKEN_KEY),
    deleteItem(AUTH_USER_KEY),
  ]);
}
