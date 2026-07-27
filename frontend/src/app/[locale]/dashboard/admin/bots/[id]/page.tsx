"use client"

import { useEffect, useState } from "react"
import { useParams } from "next/navigation"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { apiFetch, apiMutate } from "@/lib/api"

type Bot = {
  id: number
  name?: string
  platform: string
  ui_layout?: { main_menu?: { rows?: string[][] } }
  settings?: Record<string, string>
  texts?: { id: number; key: string; locale: string; value: string }[]
  required_channels?: { id: number; channel_id: string; channel_link: string; title?: string }[]
}

export default function BotStudioPage() {
  const t = useTranslations("Admin")
  const params = useParams<{ id: string }>()
  const [bot, setBot] = useState<Bot | null>(null)
  const [rows, setRows] = useState<string[][]>([["ثبت تجربه", "تجربه‌های من"], ["جستجو", "رتبه‌بندی"], ["قوانین"]])
  const [welcome, setWelcome] = useState("")
  const [rules, setRules] = useState("")
  const [force, setForce] = useState("0")
  const [channelId, setChannelId] = useState("")
  const [channelLink, setChannelLink] = useState("")

  async function reload() {
    const res = await apiFetch<{ data: Bot }>(`/api/v1/admin/bots/${params.id}`)
    setBot(res.data)
    if (res.data.ui_layout?.main_menu?.rows) setRows(res.data.ui_layout.main_menu.rows)
    setForce(res.data.settings?.force_subscribe || "0")
    const texts = res.data.texts || []
    setWelcome(texts.find((x) => x.key === "welcome")?.value || "")
    setRules(texts.find((x) => x.key === "rules")?.value || "")
  }

  useEffect(() => { reload().catch(() => undefined) }, [params.id])

  async function saveLayout() {
    await apiMutate(`/api/v1/admin/bots/${params.id}/layout`, "PUT", {
      ui_layout: { main_menu: { rows } },
    })
    toast.success(t("saveLayout"))
  }

  async function saveTexts() {
    await apiMutate(`/api/v1/admin/bots/${params.id}/texts`, "POST", { key: "welcome", value: welcome, locale: "fa" })
    await apiMutate(`/api/v1/admin/bots/${params.id}/texts`, "POST", { key: "rules", value: rules, locale: "fa" })
    await apiMutate(`/api/v1/admin/bots/${params.id}/settings`, "PUT", { settings: { force_subscribe: force } })
    toast.success(t("save"))
    await reload()
  }

  async function addRequired() {
    await apiMutate(`/api/v1/admin/bots/${params.id}/required-channels`, "POST", {
      channel_id: channelId,
      channel_link: channelLink,
    })
    setChannelId(""); setChannelLink("")
    toast.success(t("addChannel"))
    await reload()
  }

  if (!bot) return <div className="text-sm text-muted-foreground">...</div>

  return (
    <div className="grid gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader><CardTitle>{t("uiStudio")} · {bot.name || bot.platform}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <div className="text-sm font-medium">{t("mainMenu")}</div>
          {rows.map((row, ri) => (
            <div key={ri} className="flex gap-2">
              {row.map((label, ci) => (
                <Input
                  key={ci}
                  value={label}
                  onChange={(e) => {
                    const next = rows.map((r) => [...r])
                    next[ri][ci] = e.target.value
                    setRows(next)
                  }}
                />
              ))}
            </div>
          ))}
          <div className="flex gap-2">
            <Button variant="outline" onClick={() => setRows([...rows, ["", ""]])}>{t("addRow")}</Button>
            <Button onClick={saveLayout}>{t("saveLayout")}</Button>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardHeader><CardTitle>{t("botTexts")}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <Textarea value={welcome} onChange={(e) => setWelcome(e.target.value)} placeholder="welcome" rows={4} />
          <Textarea value={rules} onChange={(e) => setRules(e.target.value)} placeholder="rules" rows={6} />
          <div className="flex items-center gap-2 text-sm">
            <span>{t("forceSubscribe")}</span>
            <SelectLike value={force} onChange={setForce} />
          </div>
          <Button onClick={saveTexts}>{t("save")}</Button>
          <div className="border-t pt-3 space-y-2">
            <div className="font-medium">{t("forceJoin")}</div>
            <div className="flex flex-wrap gap-2">
              <Input value={channelId} onChange={(e) => setChannelId(e.target.value)} placeholder={t("channelId")} />
              <Input value={channelLink} onChange={(e) => setChannelLink(e.target.value)} placeholder={t("channelLink")} />
              <Button onClick={addRequired}>{t("addChannel")}</Button>
            </div>
            <ul className="text-sm space-y-1">
              {(bot.required_channels || []).map((c) => (
                <li key={c.id}>{c.title || c.channel_id} · {c.channel_link}</li>
              ))}
            </ul>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

function SelectLike({ value, onChange }: { value: string; onChange: (v: string) => void }) {
  return (
    <select className="border rounded-md px-2 py-1 bg-background" value={value} onChange={(e) => onChange(e.target.value)}>
      <option value="0">0</option>
      <option value="1">1</option>
    </select>
  )
}
