"use client"

import { useEffect, useState } from "react"
import { useLocale, useTranslations } from "next-intl"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { apiFetch } from "@/lib/api"
import { formatNumber } from "@/lib/format"
import type { Locale } from "@/i18n/config"

type Row = {
  id: number
  name: string
  reviews_count: number
  avg_overall: number
  avg_teaching: number
  score: number
}

export default function RankingsPage() {
  const t = useTranslations("Rankings")
  const locale = useLocale() as Locale
  const [rows, setRows] = useState<Row[]>([])

  useEffect(() => {
    apiFetch<{ data: Row[] }>("/api/v1/rankings").then((r) => setRows(r.data)).catch(() => setRows([]))
  }, [])

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-2xl font-bold">{t("title")}</h1>
      <Card>
        <CardHeader><CardTitle>{t("title")}</CardTitle></CardHeader>
        <CardContent className="overflow-x-auto">
          {rows.length === 0 ? (
            <p className="text-muted-foreground">{t("empty")}</p>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>#</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>{t("score")}</TableHead>
                  <TableHead>{t("reviews")}</TableHead>
                  <TableHead>{t("overall")}</TableHead>
                  <TableHead>{t("teaching")}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {rows.map((row, index) => (
                  <TableRow key={row.id}>
                    <TableCell>{formatNumber(index + 1, locale)}</TableCell>
                    <TableCell>{row.name}</TableCell>
                    <TableCell>{formatNumber(row.score, locale, 2)}</TableCell>
                    <TableCell>{formatNumber(row.reviews_count, locale)}</TableCell>
                    <TableCell>{formatNumber(row.avg_overall, locale, 2)}</TableCell>
                    <TableCell>{formatNumber(row.avg_teaching, locale, 2)}</TableCell>
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
