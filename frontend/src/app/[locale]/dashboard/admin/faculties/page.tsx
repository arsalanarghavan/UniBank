"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { apiFetch, apiMutate } from "@/lib/api"

type Uni = { id: number; name: string }
type Fac = { id: number; name: string; university_id: number; university?: Uni }

export default function FacultiesPage() {
  const t = useTranslations("Admin")
  const [unis, setUnis] = useState<Uni[]>([])
  const [items, setItems] = useState<Fac[]>([])
  const [name, setName] = useState("")
  const [uniId, setUniId] = useState("")

  async function reload() {
    const [u, f] = await Promise.all([
      apiFetch<{ data: Uni[] }>("/api/v1/universities?all=1"),
      apiFetch<{ data: Fac[] }>("/api/v1/faculties"),
    ])
    setUnis(u.data)
    setItems(f.data)
  }

  useEffect(() => { reload().catch(() => undefined) }, [])

  async function add() {
    await apiMutate("/api/v1/admin/faculties", "POST", { name, university_id: Number(uniId) })
    setName("")
    toast.success(t("save"))
    await reload()
  }

  async function remove(id: number) {
    await apiMutate(`/api/v1/admin/faculties/${id}`, "DELETE")
    toast.success(t("delete"))
    await reload()
  }

  return (
    <Card>
      <CardHeader><CardTitle>{t("faculty")}</CardTitle></CardHeader>
      <CardContent className="space-y-4">
        <div className="flex flex-wrap gap-2">
          <Select value={uniId} onValueChange={(v) => setUniId(v ?? "")}>
            <SelectTrigger className="w-56"><SelectValue placeholder={t("selectUniversity")} /></SelectTrigger>
            <SelectContent>
              {unis.map((u) => <SelectItem key={u.id} value={String(u.id)}>{u.name}</SelectItem>)}
            </SelectContent>
          </Select>
          <Input value={name} onChange={(e) => setName(e.target.value)} placeholder={t("name")} />
          <Button onClick={add}>{t("save")}</Button>
        </div>
        <ul className="space-y-2 text-sm">
          {items.map((i) => (
            <li key={i.id} className="flex items-center justify-between rounded-lg border px-3 py-2">
              <span>{i.name} · {i.university?.name}</span>
              <Button variant="outline" size="sm" onClick={() => remove(i.id)}>{t("delete")}</Button>
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  )
}
