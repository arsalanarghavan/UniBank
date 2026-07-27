"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { apiFetch, apiMutate } from "@/lib/api"

type Cat = { id: number; name: string }
type Uni = { id: number; name: string; slug: string; university_category_id: number; category?: Cat }

export default function UniversitiesPage() {
  const t = useTranslations("Admin")
  const [cats, setCats] = useState<Cat[]>([])
  const [items, setItems] = useState<Uni[]>([])
  const [name, setName] = useState("")
  const [catId, setCatId] = useState("")

  async function reload() {
    const [c, u] = await Promise.all([
      apiFetch<{ data: Cat[] }>("/api/v1/university-categories?all=1"),
      apiFetch<{ data: Uni[] }>("/api/v1/universities?all=1"),
    ])
    setCats(c.data)
    setItems(u.data)
  }

  useEffect(() => { reload().catch(() => undefined) }, [])

  async function add() {
    await apiMutate("/api/v1/admin/universities", "POST", {
      name,
      university_category_id: Number(catId),
    })
    setName("")
    toast.success(t("save"))
    await reload()
  }

  async function remove(id: number) {
    await apiMutate(`/api/v1/admin/universities/${id}`, "DELETE")
    toast.success(t("delete"))
    await reload()
  }

  return (
    <Card>
      <CardHeader><CardTitle>{t("university")}</CardTitle></CardHeader>
      <CardContent className="space-y-4">
        <div className="flex flex-wrap gap-2">
          <Select value={catId} onValueChange={(v) => setCatId(v ?? "")}>
            <SelectTrigger className="w-48"><SelectValue placeholder={t("selectCategory")} /></SelectTrigger>
            <SelectContent>
              {cats.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
            </SelectContent>
          </Select>
          <Input value={name} onChange={(e) => setName(e.target.value)} placeholder={t("name")} />
          <Button onClick={add}>{t("save")}</Button>
        </div>
        <ul className="space-y-2 text-sm">
          {items.map((i) => (
            <li key={i.id} className="flex items-center justify-between rounded-lg border px-3 py-2">
              <span>{i.name} · {i.category?.name}</span>
              <Button variant="outline" size="sm" onClick={() => remove(i.id)}>{t("delete")}</Button>
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  )
}
