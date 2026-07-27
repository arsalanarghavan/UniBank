"use client";

import { useState } from "react";
import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { GalleryVerticalEnd } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Field, FieldDescription, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { apiMutate } from "@/lib/api";
import { LocaleThemeControls } from "@/components/locale/locale-theme-controls";

export default function RegisterPage() {
  const t = useTranslations("Auth");
  const tApp = useTranslations("App");
  const locale = useLocale();
  const router = useRouter();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await apiMutate<{ data: unknown; token?: string }>("/api/v1/auth/register", "POST", {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        locale,
      });
      if (res.token) window.localStorage.setItem("ostadbank_token", res.token);
      router.push(`/${locale}/dashboard`);
      router.refresh();
    } catch (err) {
      toast.error(err instanceof Error ? err.message : t("registerFailed"));
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="bg-muted flex min-h-svh flex-col items-center justify-center p-6 md:p-10">
      <div className="flex w-full max-w-md flex-col gap-6">
        <div className="flex items-center justify-between">
          <a href={`/${locale}`} className="flex items-center gap-2 font-medium">
            <div className="bg-primary text-primary-foreground flex size-6 items-center justify-center rounded-md">
              <GalleryVerticalEnd className="size-4" />
            </div>
            {tApp("name")}
          </a>
          <LocaleThemeControls />
        </div>
        <Card>
          <CardContent className="p-6 md:p-8">
            <form onSubmit={onSubmit}>
              <FieldGroup>
                <div className="flex flex-col items-center gap-2 text-center">
                  <h1 className="text-2xl font-bold">{t("register")}</h1>
                  <p className="text-muted-foreground text-balance">{t("registerSubtitle")}</p>
                </div>
                <Field>
                  <FieldLabel htmlFor="name">{t("name")}</FieldLabel>
                  <Input id="name" value={name} onChange={(e) => setName(e.target.value)} required />
                </Field>
                <Field>
                  <FieldLabel htmlFor="email">{t("email")}</FieldLabel>
                  <Input id="email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
                </Field>
                <Field>
                  <FieldLabel htmlFor="password">{t("password")}</FieldLabel>
                  <Input id="password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
                </Field>
                <Field>
                  <FieldLabel htmlFor="password_confirmation">{t("confirmPassword")}</FieldLabel>
                  <Input
                    id="password_confirmation"
                    type="password"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    required
                  />
                </Field>
                <Field>
                  <Button type="submit" disabled={loading} className="w-full">
                    {t("register")}
                  </Button>
                </Field>
                <FieldDescription className="text-center">
                  {t("hasAccount")}{" "}
                  <Link href={`/${locale}/login`} className="underline underline-offset-2">
                    {t("signIn")}
                  </Link>
                </FieldDescription>
              </FieldGroup>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
