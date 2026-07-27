"use client"

import { useEffect, useState } from "react"
import { useLocale, useTranslations } from "next-intl"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { apiFetch } from "@/lib/api"
import { formatNumber } from "@/lib/format"
import type { Locale } from "@/i18n/config"
import {
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
  type ChartConfig,
} from "@/components/ui/chart"
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts"

type Stats = {
  data: {
    users_count: number
    professors_count: number
    experiences_total: number
    experiences_by_status: { pending: number; approved: number; rejected: number }
    monthly_experiences: { month: string; total: number }[]
  }
}

type Me = { data: { name: string; roles: string[] } }

const chartConfig = {
  total: { label: "Total", color: "var(--chart-1)" },
} satisfies ChartConfig

export default function DashboardPage() {
  const t = useTranslations("Dashboard")
  const locale = useLocale() as Locale
  const [me, setMe] = useState<Me["data"] | null>(null)
  const [stats, setStats] = useState<Stats["data"] | null>(null)

  useEffect(() => {
    apiFetch<Me>("/api/v1/auth/me").then((r) => setMe(r.data)).catch(() => setMe(null))
  }, [])

  useEffect(() => {
    if (!me) return
    const isAdmin = me.roles?.includes("admin") || me.roles?.includes("owner")
    if (!isAdmin) return
    apiFetch<Stats>("/api/v1/admin/stats").then((r) => setStats(r.data)).catch(() => setStats(null))
  }, [me])

  const cards = stats
    ? [
        { title: t("users"), value: stats.users_count },
        { title: t("professors"), value: stats.professors_count },
        { title: t("pending"), value: stats.experiences_by_status.pending },
        { title: t("approved"), value: stats.experiences_by_status.approved },
      ]
    : []

  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">{t("title")}</h1>
        <p className="text-muted-foreground">
          {me ? t("welcome", { name: me.name }) : t("overview")}
        </p>
      </div>

      {stats ? (
        <>
          <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => (
              <Card key={card.title}>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm font-medium text-muted-foreground">{card.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-3xl font-bold">{formatNumber(card.value, locale)}</div>
                </CardContent>
              </Card>
            ))}
          </div>
          <Card>
            <CardHeader>
              <CardTitle>{t("monthly")}</CardTitle>
            </CardHeader>
            <CardContent>
              <ChartContainer config={chartConfig} className="min-h-[260px] w-full">
                <BarChart data={stats.monthly_experiences}>
                  <CartesianGrid vertical={false} />
                  <XAxis dataKey="month" tickLine={false} axisLine={false} />
                  <YAxis allowDecimals={false} />
                  <ChartTooltip content={<ChartTooltipContent />} />
                  <Bar dataKey="total" fill="var(--color-total)" radius={6} />
                </BarChart>
              </ChartContainer>
            </CardContent>
          </Card>
        </>
      ) : (
        <Card>
          <CardContent className="py-8 text-muted-foreground">{t("overview")}</CardContent>
        </Card>
      )}
    </div>
  )
}
