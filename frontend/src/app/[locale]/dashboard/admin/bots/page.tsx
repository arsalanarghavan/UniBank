"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { useLocale, useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { apiFetch, apiMutate } from "@/lib/api"

type Bot = {
  id: number
  platform: string
  name?: string
  username?: string
  university_id: number
  is_enabled: boolean
  university?: { name: string }
  webhook_secret?: string
}

export default function BotsPage() {
  const t = useTranslations("Admin")
  const locale = useLocale()
  const [bots, setBots] = useState<Bot[]>([])
  const [unis, setUnis] = useState<{ id: number; name: string }[]>([])
  const [uniId, setUniId] = useState("")
  const [platform, setPlatform] = useState("telegram")
  const [token, setToken] = useState("")
  const [username, setUsername] = useState("")
  const [name, setName] = useState("")

  async function reload() {
    const [b, u] = await Promise.all([
      apiFetch<{ data: Bot[] }>("/api/v1/admin/bots"),
      apiFetch<{ data: { id: number; name: string }[] }>("/api/v1/universities?all=1"),
    ])
    setBots(b.data)
    setUnis(u.data)
  }

  useEffect(() => { reload().catch(() => undefined) }, [])

  async function add() {
    await apiMutate("/api/v1/admin/bots", "POST", {
      university_id: Number(uniId),
      platform,
      token: token || null,
      username: username || null,
      name: name || null,
    })
    setToken(""); setUsername(""); setName("")
    toast.success(t("addBot"))
    await reload()
  }

  async function remove(id: number) {
    await apiMutate(`/api/v1/admin/bots/${id}`, "DELETE")
    toast.success(t("delete"))
    await reload()
  }

  return (
    <div className="space-y-4">
      <Card>
        <CardHeader><CardTitle>{t("addBot")}</CardTitle></CardHeader>
        <CardContent className="flex flex-wrap gap-2">
          <Select value={uniId} onValueChange={(v) => setUniId(v ?? "")}>
            <SelectTrigger className="w-48"><SelectValue placeholder={t("selectUniversity")} /></SelectTrigger>
            <SelectContent>
              {unis.map((u) => <SelectItem key={u.id} value={String(u.id)}>{u.name}</SelectItem>)}
            </SelectContent>
          </Select>
          <Select value={platform} onValueChange={(v) => setPlatform(v ?? "")}>
            <SelectTrigger className="w-36"><SelectValue /></SelectTrigger>
            <SelectContent>
              <SelectItem value="telegram">{t("telegram")}</SelectItem>
              <SelectItem value="bale">{t("bale")}</SelectItem>
            </SelectContent>
          </Select>
          <Input value={name} onChange={(e) => setName(e.target.value)} placeholder={t("name")} />
          <Input value={username} onChange={(e) => setUsername(e.target.value)} placeholder={t("username")} />
          <Input value={token} onChange={(e) => setToken(e.target.value)} placeholder={t("token")} />
          <Button onClick={add}>{t("save")}</Button>
        </CardContent>
      </Card>
      <ul className="space-y-2">
        {bots.map((b) => (
          <li key={b.id} className="flex flex-wrap items-center justify-between gap-2 rounded-lg border px-3 py-3 text-sm">
            <div>
              <div className="font-medium">{b.name || b.username || `#${b.id}`} · {b.platform}</div>
              <div className="text-muted-foreground">{b.university?.name}</div>
              {b.webhook_secret && (
                <div className="mt-1 text-xs break-all">{t("webhookSecret")}: {b.webhook_secret}</div>
              )}
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                render={<Link href={`/${locale}/dashboard/admin/bots/${b.id}`} />}
              >
                {t("uiStudio")}
              </Button>
              <Button variant="outline" size="sm" onClick={() => remove(b.id)}>{t("delete")}</Button>
            </div>
          </li>
        ))}
      </ul>
    </div>
  )
}
