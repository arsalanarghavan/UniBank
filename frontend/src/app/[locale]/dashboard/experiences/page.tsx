"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { useLocale, useTranslations } from "next-intl"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { apiFetch } from "@/lib/api"
import { formatDate } from "@/lib/format"
import type { Locale } from "@/i18n/config"

type Item = {
  id: number
  status: string
  overall_rating: number
  professor?: { name: string }
  course?: { name: string }
  created_at: string
}

export default function ExperiencesPage() {
  const t = useTranslations("Experiences")
  const tCommon = useTranslations("Common")
  const locale = useLocale() as Locale
  const [items, setItems] = useState<Item[]>([])

  useEffect(() => {
    apiFetch<{ data: Item[] | { data: Item[] } }>("/api/v1/experiences")
      .then((r) => {
        const raw = r.data
        setItems(Array.isArray(raw) ? raw : (raw?.data ?? []))
      })
      .catch(() => setItems([]))
  }, [])

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <h1 className="text-2xl font-bold">{t("title")}</h1>
        <Button render={<Link href={`/${locale}/dashboard/submit`} />}>
          {t("submit")}
        </Button>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>{t("title")}</CardTitle>
        </CardHeader>
        <CardContent className="overflow-x-auto">
          {items.length === 0 ? (
            <p className="text-muted-foreground">{t("empty")}</p>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>{t("professor")}</TableHead>
                  <TableHead>{t("course")}</TableHead>
                  <TableHead>{t("status")}</TableHead>
                  <TableHead>{t("overallRating")}</TableHead>
                  <TableHead>{tCommon("date")}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {items.map((item) => (
                  <TableRow key={item.id}>
                    <TableCell>{item.professor?.name || "-"}</TableCell>
                    <TableCell>{item.course?.name || "-"}</TableCell>
                    <TableCell><Badge variant="secondary">{item.status}</Badge></TableCell>
                    <TableCell>{item.overall_rating}</TableCell>
                    <TableCell>{formatDate(item.created_at, locale)}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
