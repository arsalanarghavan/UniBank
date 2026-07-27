"use client";

import { useState } from "react";
import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import {
  Field,
  FieldDescription,
  FieldGroup,
  FieldLabel,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { apiMutate } from "@/lib/api";
import { LocaleThemeControls } from "@/components/locale/locale-theme-controls";

export function LoginForm({
  className,
  ...props
}: React.ComponentProps<"div">) {
  const t = useTranslations("Auth");
  const tApp = useTranslations("App");
  const locale = useLocale();
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await apiMutate<{ data: unknown; token?: string }>("/api/v1/auth/login", "POST", {
        email,
        password,
      });
      if (res.token) window.localStorage.setItem("ostadbank_token", res.token);
      router.push(`/${locale}/dashboard`);
      router.refresh();
    } catch (err) {
      toast.error(err instanceof Error ? err.message : t("loginFailed"));
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className={cn("flex flex-col gap-6", className)} {...props}>
      <div className="flex justify-end">
        <LocaleThemeControls />
      </div>
      <Card className="overflow-hidden p-0">
        <CardContent className="grid p-0 md:grid-cols-2">
          <form className="p-6 md:p-8" onSubmit={onSubmit}>
            <FieldGroup>
              <div className="flex flex-col items-center gap-2 text-center">
                <h1 className="text-2xl font-bold">{tApp("name")}</h1>
                <p className="text-balance text-muted-foreground">{t("loginSubtitle")}</p>
              </div>
              <Field>
                <FieldLabel htmlFor="email">{t("email")}</FieldLabel>
                <Input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  autoComplete="email"
                />
              </Field>
              <Field>
                <FieldLabel htmlFor="password">{t("password")}</FieldLabel>
                <Input
                  id="password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  autoComplete="current-password"
                />
              </Field>
              <Field>
                <Button type="submit" disabled={loading} className="w-full">
                  {t("login")}
                </Button>
              </Field>
              <FieldDescription className="text-center">
                {t("noAccount")}{" "}
                <Link href={`/${locale}/register`} className="underline underline-offset-2">
                  {t("signUp")}
                </Link>
              </FieldDescription>
            </FieldGroup>
          </form>
          <div className="relative hidden bg-muted md:block">
            {/* Decorative brand hero from local SVG — Priority Hints via fetchPriority */}
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src="/login-hero.svg"
              alt={tApp("name")}
              className="absolute inset-0 h-full w-full object-cover dark:brightness-[0.85]"
              fetchPriority="high"
            />
          </div>
        </CardContent>
      </Card>
      <FieldDescription className="px-6 text-center">
        {t("termsPrefix")} <a href={`/${locale}/dashboard/rules`}>{t("terms")}</a>{" "}
        &amp; <a href={`/${locale}/dashboard/rules`}>{t("privacy")}</a>.
      </FieldDescription>
    </div>
  );
}
