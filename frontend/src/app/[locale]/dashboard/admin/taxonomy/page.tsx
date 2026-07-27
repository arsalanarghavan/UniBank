"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { apiFetch, apiMutate } from "@/lib/api"

type FieldNode = {
  id: number
  name: string
  majors?: { id: number; name: string; courses?: { id: number; name: string }[] }[]
}

export default function TaxonomyPage() {
  const t = useTranslations("Admin")
  const te = useTranslations("Experiences")
  const [fields, setFields] = useState<FieldNode[]>([])
  const [professors, setProfessors] = useState<{ id: number; name: string }[]>([])
  const [fieldName, setFieldName] = useState("")
  const [professorName, setProfessorName] = useState("")

  async function reload() {
    const f = await apiFetch<{ data: FieldNode[] }>("/api/v1/fields")
    setFields(f.data)
    const p = await apiFetch<{ data: { id: number; name: string }[] | { data: { id: number; name: string }[] } }>("/api/v1/professors")
    const raw = p.data
    setProfessors(Array.isArray(raw) ? raw : raw?.data ?? [])
  }

  useEffect(() => {
    reload().catch(() => undefined)
  }, [])

  async function addField() {
    await apiMutate("/api/v1/admin/fields", "POST", { name: fieldName })
    setFieldName("")
    toast.success(t("addField"))
    await reload()
  }

  async function addProfessor() {
    await apiMutate("/api/v1/admin/professors", "POST", { name: professorName })
    setProfessorName("")
    toast.success(t("addProfessor"))
    await reload()
  }

  return (
    <div className="grid gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader><CardTitle>{te("field")}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <div className="flex gap-2">
            <Input value={fieldName} onChange={(e) => setFieldName(e.target.value)} placeholder={t("name")} />
            <Button onClick={addField}>{t("addField")}</Button>
          </div>
          <ul className="space-y-2 text-sm">
            {fields.map((f) => (
              <li key={f.id} className="rounded-lg border px-3 py-2">
                <div className="font-medium">{f.name}</div>
                <div className="text-muted-foreground">
                  {(f.majors || []).map((m) => m.name).join(" · ")}
                </div>
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>
      <Card>
        <CardHeader><CardTitle>{te("professor")}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <div className="flex gap-2">
            <Input value={professorName} onChange={(e) => setProfessorName(e.target.value)} placeholder={t("name")} />
            <Button onClick={addProfessor}>{t("addProfessor")}</Button>
          </div>
          <ul className="space-y-2 text-sm">
            {professors.map((p) => (
              <li key={p.id} className="rounded-lg border px-3 py-2">{p.name}</li>
            ))}
          </ul>
        </CardContent>
      </Card>
    </div>
  )
}
