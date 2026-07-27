"use client";

import * as React from "react";
import { useTranslations } from "next-intl";
import { useTheme } from "next-themes";
import { usePathname, useRouter } from "next/navigation";
import { MoonIcon, SunIcon, LanguagesIcon, PaletteIcon } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import type { Locale } from "@/i18n/config";

const accents = [
  { id: "neutral", label: "Neutral" },
  { id: "blue", label: "Blue" },
  { id: "green", label: "Green" },
  { id: "orange", label: "Orange" },
  { id: "rose", label: "Rose" },
] as const;

export function LocaleThemeControls() {
  const t = useTranslations("Common");
  const router = useRouter();
  const pathname = usePathname();
  const { setTheme } = useTheme();
  const [accent, setAccent] = React.useState("neutral");

  React.useEffect(() => {
    const saved = window.localStorage.getItem("ostadbank-accent") || "neutral";
    setAccent(saved);
    document.documentElement.dataset.accent = saved;
  }, []);

  function switchLocale(next: Locale) {
    const segments = pathname.split("/");
    segments[1] = next;
    router.replace(segments.join("/") || `/${next}`);
  }

  function applyAccent(id: string) {
    setAccent(id);
    document.documentElement.dataset.accent = id;
    window.localStorage.setItem("ostadbank-accent", id);
  }

  return (
    <div className="flex items-center gap-1">
      <DropdownMenu>
        <DropdownMenuTrigger render={<Button variant="ghost" size="icon" aria-label={t("language")} />}>
          <LanguagesIcon className="size-4" />
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuLabel>{t("language")}</DropdownMenuLabel>
          <DropdownMenuItem onClick={() => switchLocale("fa")}>فارسی</DropdownMenuItem>
          <DropdownMenuItem onClick={() => switchLocale("en")}>English</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>

      <DropdownMenu>
        <DropdownMenuTrigger render={<Button variant="ghost" size="icon" aria-label={t("theme")} />}>
          <SunIcon className="size-4 scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90" />
          <MoonIcon className="absolute size-4 scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0" />
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuLabel>{t("theme")}</DropdownMenuLabel>
          <DropdownMenuItem onClick={() => setTheme("light")}>{t("light")}</DropdownMenuItem>
          <DropdownMenuItem onClick={() => setTheme("dark")}>{t("dark")}</DropdownMenuItem>
          <DropdownMenuItem onClick={() => setTheme("system")}>{t("system")}</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>

      <DropdownMenu>
        <DropdownMenuTrigger render={<Button variant="ghost" size="icon" aria-label={t("accent")} />}>
          <PaletteIcon className="size-4" />
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuLabel>{t("accent")}</DropdownMenuLabel>
          <DropdownMenuSeparator />
          {accents.map((item) => (
            <DropdownMenuItem
              key={item.id}
              onClick={() => applyAccent(item.id)}
              className={accent === item.id ? "bg-accent" : undefined}
            >
              {item.label}
            </DropdownMenuItem>
          ))}
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  );
}
