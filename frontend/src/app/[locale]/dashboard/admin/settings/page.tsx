"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Switch } from "@/components/ui/switch"
import { apiFetch, apiMutate } from "@/lib/api"

export default function AdminSettingsPage() {
  const t = useTranslations("Admin")
  const [forceSubscribe, setForceSubscribe] = useState(false)
  const [channels, setChannels] = useState<{ id: number; channel_id: string; channel_link: string }[]>([])
  const [channelId, setChannelId] = useState("")
  const [channelLink, setChannelLink] = useState("")

  async function load() {
    const res = await apiFetch<{
      data: {
        settings: Record<string, string>
        channels: { id: number; channel_id: string; channel_link: string }[]
      }
    }>("/api/v1/admin/settings")
    setForceSubscribe(res.data.settings.force_subscribe === "1")
    setChannels(res.data.channels)
  }

  useEffect(() => {
    load().catch(() => undefined)
  }, [])

  async function saveForce(next: boolean) {
    setForceSubscribe(next)
    await apiMutate("/api/v1/admin/settings", "PUT", {
      key: "force_subscribe",
      value: next ? "1" : "0",
    })
    toast.success(t("forceSubscribe"))
  }

  async function addChannel() {
    await apiMutate("/api/v1/admin/channels", "POST", {
      channel_id: channelId,
      channel_link: channelLink,
    })
    setChannelId("")
    setChannelLink("")
    toast.success(t("addChannel"))
    await load()
  }

  return (
    <div className="grid gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader><CardTitle>{t("forceSubscribe")}</CardTitle></CardHeader>
        <CardContent>
          <div className="flex items-center justify-between gap-4">
            <span className="text-sm">{t("forceSubscribe")}</span>
            <Switch checked={forceSubscribe} onCheckedChange={saveForce} />
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardHeader><CardTitle>{t("channels")}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <Input value={channelId} onChange={(e) => setChannelId(e.target.value)} placeholder={t("channelId")} />
          <Input value={channelLink} onChange={(e) => setChannelLink(e.target.value)} placeholder={t("channelLink")} />
          <Button onClick={addChannel}>{t("addChannel")}</Button>
          <ul className="space-y-2 text-sm">
            {channels.map((c) => (
              <li key={c.id} className="rounded-lg border px-3 py-2">
                {c.channel_id} — {c.channel_link}
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>
    </div>
  )
}
