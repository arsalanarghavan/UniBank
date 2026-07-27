"use client"

import { useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { apiMutate } from "@/lib/api"

export default function BroadcastPage() {
  const t = useTranslations("Admin")
  const [message, setMessage] = useState("")
  const [telegramId, setTelegramId] = useState("")
  const [dm, setDm] = useState("")

  async function sendBroadcast() {
    await apiMutate("/api/v1/admin/broadcast", "POST", { message })
    toast.success(t("sendBroadcast"))
    setMessage("")
  }

  async function sendDm() {
    await apiMutate("/api/v1/admin/direct-message", "POST", {
      telegram_id: Number(telegramId),
      message: dm,
    })
    toast.success(t("sendDm"))
    setDm("")
  }

  return (
    <div className="grid gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader><CardTitle>{t("sendBroadcast")}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <Textarea value={message} onChange={(e) => setMessage(e.target.value)} placeholder={t("message")} />
          <Button onClick={sendBroadcast}>{t("sendBroadcast")}</Button>
        </CardContent>
      </Card>
      <Card>
        <CardHeader><CardTitle>{t("sendDm")}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <Input value={telegramId} onChange={(e) => setTelegramId(e.target.value)} placeholder={t("telegramId")} />
          <Textarea value={dm} onChange={(e) => setDm(e.target.value)} placeholder={t("message")} />
          <Button onClick={sendDm}>{t("sendDm")}</Button>
        </CardContent>
      </Card>
    </div>
  )
}
