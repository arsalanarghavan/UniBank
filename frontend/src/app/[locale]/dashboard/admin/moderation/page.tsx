"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Textarea } from "@/components/ui/textarea"
import { apiFetch, apiMutate } from "@/lib/api"

type Item = {
  id: number
  status: string
  conclusion: string
  professor?: { name: string }
  user?: { name: string }
}

export default function ModerationPage() {
  const t = useTranslations("Admin")
  const [items, setItems] = useState<Item[]>([])
  const [reasons, setReasons] = useState<Record<number, string>>({})

  async function load() {
    const res = await apiFetch<{ data: Item[] | { data: Item[] } }>("/api/v1/admin/moderation/pending")
    const raw = res.data
    setItems(Array.isArray(raw) ? raw : raw?.data ?? [])
  }

  useEffect(() => {
    load().catch(() => setItems([]))
  }, [])

  async function approve(id: number) {
    await apiMutate(`/api/v1/admin/moderation/${id}/approve`, "POST")
    toast.success(t("approve"))
    await load()
  }

  async function reject(id: number) {
    await apiMutate(`/api/v1/admin/moderation/${id}/reject`, "POST", {
      reason: reasons[id] || "",
    })
    toast.success(t("reject"))
    await load()
  }

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-2xl font-bold">{t("pendingQueue")}</h1>
      {items.map((item) => (
        <Card key={item.id}>
          <CardHeader className="flex flex-row items-center justify-between gap-2">
            <CardTitle className="text-base">
              #{item.id} — {item.professor?.name} ({item.user?.name})
            </CardTitle>
            <Badge>{item.status}</Badge>
          </CardHeader>
          <CardContent className="space-y-3">
            <p className="text-sm">{item.conclusion}</p>
            <Textarea
              placeholder={t("reason")}
              value={reasons[item.id] || ""}
              onChange={(e) => setReasons((prev) => ({ ...prev, [item.id]: e.target.value }))}
            />
            <div className="flex gap-2">
              <Button onClick={() => approve(item.id)}>{t("approve")}</Button>
              <Button variant="destructive" onClick={() => reject(item.id)}>{t("reject")}</Button>
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  )
}
