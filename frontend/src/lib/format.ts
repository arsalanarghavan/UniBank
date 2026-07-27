import { format as formatGregorian } from "date-fns";
import { format as formatJalali } from "date-fns-jalali";
import type { Locale } from "@/i18n/config";

const persianDigits = "۰۱۲۳۴۵۶۷۸۹";

export function toLocaleDigits(value: string | number, locale: Locale): string {
  const str = String(value);
  if (locale !== "fa") return str;
  return str.replace(/\d/g, (d) => persianDigits[Number(d)] ?? d);
}

export function formatDate(
  value: string | Date | null | undefined,
  locale: Locale,
  pattern = "yyyy/MM/dd",
): string {
  if (!value) return "-";
  const date = typeof value === "string" ? new Date(value) : value;
  if (Number.isNaN(date.getTime())) return "-";
  const formatted =
    locale === "fa" ? formatJalali(date, pattern) : formatGregorian(date, pattern);
  return toLocaleDigits(formatted, locale);
}

export function formatNumber(value: number, locale: Locale, digits = 0): string {
  const formatted = value.toLocaleString(locale === "fa" ? "fa-IR" : "en-US", {
    maximumFractionDigits: digits,
    minimumFractionDigits: digits,
  });
  return formatted;
}
