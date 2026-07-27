"use client"

import { useState } from "react"
import { useTranslations } from "next-intl"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { apiFetch } from "@/lib/api"

export default function SearchPage() {
  const t = useTranslations("Search")
  const [q, setQ] = useState("")
  const [professors, setProfessors] = useState<{ id: number; name: string }[]>([])
  const [courses, setCourses] = useState<{ id: number; name: string }[]>([])
  const [loading, setLoading] = useState(false)

  async function onSearch(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    try {
      const res = await apiFetch<{
        data: { professors: { id: number; name: string }[]; courses: { id: number; name: string }[] }
      }>(`/api/v1/search?q=${encodeURIComponent(q)}`)
      setProfessors(res.data.professors)
      setCourses(res.data.courses)
    } catch {
      setProfessors([])
      setCourses([])
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-2xl font-bold">{t("title")}</h1>
      <form onSubmit={onSearch} className="flex flex-col gap-2 sm:flex-row">
        <Input
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder={t("placeholder")}
          className="flex-1"
        />
        <Button type="submit" disabled={loading}>{t("title")}</Button>
      </form>
      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader><CardTitle>{t("professors")}</CardTitle></CardHeader>
          <CardContent className="space-y-2">
            {professors.length === 0 ? <p className="text-muted-foreground">{t("empty")}</p> : professors.map((p) => (
              <div key={p.id} className="rounded-lg border px-3 py-2">{p.name}</div>
            ))}
          </CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>{t("courses")}</CardTitle></CardHeader>
          <CardContent className="space-y-2">
            {courses.length === 0 ? <p className="text-muted-foreground">{t("empty")}</p> : courses.map((c) => (
              <div key={c.id} className="rounded-lg border px-3 py-2">{c.name}</div>
            ))}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
