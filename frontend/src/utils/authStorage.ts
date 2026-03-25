const AUTH_TOKEN_KEY = "authToken";
const USER_KEY = "user";

const parseUser = (raw: string | null) => {
  if (!raw) return null;

  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
};

export const getAuthToken = (): string | null => {
  return localStorage.getItem(AUTH_TOKEN_KEY) || sessionStorage.getItem(AUTH_TOKEN_KEY);
};

export const getStoredUser = <T = any>(): T | null => {
  const rawUser = localStorage.getItem(USER_KEY) || sessionStorage.getItem(USER_KEY);
  return parseUser(rawUser) as T | null;
};

export const saveAuthData = (token: string, user: unknown, rememberMe: boolean): void => {
  const storage = rememberMe ? localStorage : sessionStorage;
  const oppositeStorage = rememberMe ? sessionStorage : localStorage;

  oppositeStorage.removeItem(AUTH_TOKEN_KEY);
  oppositeStorage.removeItem(USER_KEY);

  storage.setItem(AUTH_TOKEN_KEY, token);
  storage.setItem(USER_KEY, JSON.stringify(user));
};

export const clearAuthData = (): void => {
  localStorage.removeItem(AUTH_TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  sessionStorage.removeItem(AUTH_TOKEN_KEY);
  sessionStorage.removeItem(USER_KEY);
};
