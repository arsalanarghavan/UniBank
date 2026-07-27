"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { apiFetch } from "@/lib/api"

export default function RulesPage() {
  const t = useTranslations("Rules")
  const [rules, setRules] = useState("")

  useEffect(() => {
    apiFetch<{ data: { rules: string } }>("/api/v1/rules")
      .then((r) => setRules(r.data.rules))
      .catch(() => setRules(""))
  }, [])

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-2xl font-bold">{t("title")}</h1>
      <Card>
        <CardHeader><CardTitle>{t("title")}</CardTitle></CardHeader>
        <CardContent>
          <pre className="whitespace-pre-wrap font-sans text-sm leading-7">{rules}</pre>
        </CardContent>
      </Card>
    </div>
  )
}
