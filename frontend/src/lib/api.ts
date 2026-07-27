const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

export type ApiError = {
  message: string;
  errors?: Record<string, string[]>;
};

async function parseJson(response: Response) {
  const text = await response.text();
  try {
    return text ? JSON.parse(text) : {};
  } catch {
    return { message: text };
  }
}

export async function apiFetch<T = unknown>(
  path: string,
  init: RequestInit = {},
): Promise<T> {
  const headers = new Headers(init.headers || {});
  if (!headers.has("Accept")) headers.set("Accept", "application/json");
  if (init.body && !headers.has("Content-Type")) {
    headers.set("Content-Type", "application/json");
  }
  headers.set("X-Requested-With", "XMLHttpRequest");
  if (typeof window !== "undefined") {
    const token = window.localStorage.getItem("ostadbank_token");
    if (token && !headers.has("Authorization")) {
      headers.set("Authorization", `Bearer ${token}`);
    }
  }

  const response = await fetch(`${API_URL}${path}`, {
    ...init,
    headers,
    credentials: "include",
    cache: "no-store",
  });

  const data = await parseJson(response);

  if (!response.ok) {
    const error = new Error(
      data.message || data.error || `Request failed (${response.status})`,
    ) as Error & ApiError;
    error.errors = data.errors;
    throw error;
  }

  return data as T;
}

export async function ensureCsrfCookie(): Promise<void> {
  await fetch(`${API_URL}/sanctum/csrf-cookie`, {
    credentials: "include",
    cache: "no-store",
  });
}

function getXsrfToken(): string | null {
  if (typeof document === "undefined") return null;
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

export async function apiMutate<T = unknown>(
  path: string,
  method: "POST" | "PUT" | "PATCH" | "DELETE",
  body?: unknown,
): Promise<T> {
  await ensureCsrfCookie();
  const headers: HeadersInit = {};
  const xsrf = getXsrfToken();
  if (xsrf) headers["X-XSRF-TOKEN"] = xsrf;

  return apiFetch<T>(path, {
    method,
    headers,
    body: body === undefined ? undefined : JSON.stringify(body),
  });
}

export async function apiUpload<T = unknown>(
  path: string,
  formData: FormData,
): Promise<T> {
  await ensureCsrfCookie();
  const headers = new Headers();
  headers.set("Accept", "application/json");
  headers.set("X-Requested-With", "XMLHttpRequest");
  const xsrf = getXsrfToken();
  if (xsrf) headers.set("X-XSRF-TOKEN", xsrf);
  if (typeof window !== "undefined") {
    const token = window.localStorage.getItem("ostadbank_token");
    if (token) headers.set("Authorization", `Bearer ${token}`);
  }

  const response = await fetch(`${API_URL}${path}`, {
    method: "POST",
    headers,
    body: formData,
    credentials: "include",
    cache: "no-store",
  });

  const data = await parseJson(response);
  if (!response.ok) {
    const error = new Error(
      data.message || data.error || `Request failed (${response.status})`,
    ) as Error & ApiError;
    error.errors = data.errors;
    throw error;
  }
  return data as T;
}

export { API_URL };
