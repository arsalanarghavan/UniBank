"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { apiFetch, apiMutate } from "@/lib/api"

type Item = { id: number; name: string; name_en?: string; slug: string; sort_order: number }

export default function DegreeLevelsPage() {
  const t = useTranslations("Admin")
  const [items, setItems] = useState<Item[]>([])
  const [name, setName] = useState("")
  const [nameEn, setNameEn] = useState("")

  async function reload() {
    const res = await apiFetch<{ data: Item[] }>("/api/v1/degree-levels?all=1")
    setItems(res.data)
  }

  useEffect(() => { reload().catch(() => undefined) }, [])

  async function add() {
    await apiMutate("/api/v1/admin/degree-levels", "POST", { name, name_en: nameEn || null })
    setName(""); setNameEn("")
    toast.success(t("save"))
    await reload()
  }

  async function remove(id: number) {
    await apiMutate(`/api/v1/admin/degree-levels/${id}`, "DELETE")
    toast.success(t("delete"))
    await reload()
  }

  return (
    <Card>
      <CardHeader><CardTitle>{t("degreeLevel")}</CardTitle></CardHeader>
      <CardContent className="space-y-4">
        <div className="flex flex-wrap gap-2">
          <Input value={name} onChange={(e) => setName(e.target.value)} placeholder={t("name")} />
          <Input value={nameEn} onChange={(e) => setNameEn(e.target.value)} placeholder={t("nameEn")} />
          <Button onClick={add}>{t("save")}</Button>
        </div>
        <ul className="space-y-2 text-sm">
          {items.map((i) => (
            <li key={i.id} className="flex items-center justify-between rounded-lg border px-3 py-2">
              <span>{i.name} {i.name_en ? `(${i.name_en})` : ""}</span>
              <Button variant="outline" size="sm" onClick={() => remove(i.id)}>{t("delete")}</Button>
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  )
}
