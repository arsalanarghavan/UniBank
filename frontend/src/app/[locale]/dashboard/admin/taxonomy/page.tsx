"use client"

import { useEffect, useMemo, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { apiFetch, apiMutate } from "@/lib/api"

type Fac = { id: number; name: string; university_id: number }
type Degree = { id: number; name: string }
type FieldNode = {
  id: number
  name: string
  faculty_id?: number
  majors?: { id: number; name: string; courses?: { id: number; name: string; degree_level_id?: number }[] }[]
}

export default function TaxonomyPage() {
  const t = useTranslations("Admin")
  const te = useTranslations("Experiences")
  const [faculties, setFaculties] = useState<Fac[]>([])
  const [degrees, setDegrees] = useState<Degree[]>([])
  const [facultyId, setFacultyId] = useState("")
  const [fields, setFields] = useState<FieldNode[]>([])
  const [fieldName, setFieldName] = useState("")
  const [majorName, setMajorName] = useState("")
  const [courseName, setCourseName] = useState("")
  const [fieldId, setFieldId] = useState("")
  const [majorId, setMajorId] = useState("")
  const [degreeId, setDegreeId] = useState("")

  async function reload() {
    const [facs, degs] = await Promise.all([
      apiFetch<{ data: Fac[] }>("/api/v1/faculties"),
      apiFetch<{ data: Degree[] }>("/api/v1/degree-levels"),
    ])
    setFaculties(facs.data)
    setDegrees(degs.data)
    if (facultyId) {
      const f = await apiFetch<{ data: FieldNode[] }>(`/api/v1/fields?faculty_id=${facultyId}`)
      setFields(f.data)
    }
  }

  useEffect(() => { reload().catch(() => undefined) }, [])
  useEffect(() => {
    if (!facultyId) { setFields([]); return }
    apiFetch<{ data: FieldNode[] }>(`/api/v1/fields?faculty_id=${facultyId}`)
      .then((r) => setFields(r.data))
      .catch(() => setFields([]))
  }, [facultyId])

  const majors = useMemo(() => fields.find((f) => String(f.id) === fieldId)?.majors || [], [fields, fieldId])

  async function addField() {
    await apiMutate("/api/v1/admin/fields", "POST", { name: fieldName, faculty_id: Number(facultyId) })
    setFieldName("")
    toast.success(t("addField"))
    const f = await apiFetch<{ data: FieldNode[] }>(`/api/v1/fields?faculty_id=${facultyId}`)
    setFields(f.data)
  }

  async function addMajor() {
    await apiMutate("/api/v1/admin/majors", "POST", { name: majorName, field_id: Number(fieldId) })
    setMajorName("")
    toast.success(t("addMajor"))
    const f = await apiFetch<{ data: FieldNode[] }>(`/api/v1/fields?faculty_id=${facultyId}`)
    setFields(f.data)
  }

  async function addCourse() {
    await apiMutate("/api/v1/admin/courses", "POST", {
      name: courseName,
      major_id: Number(majorId),
      degree_level_id: degreeId ? Number(degreeId) : null,
    })
    setCourseName("")
    toast.success(t("addCourse"))
    const f = await apiFetch<{ data: FieldNode[] }>(`/api/v1/fields?faculty_id=${facultyId}`)
    setFields(f.data)
  }

  return (
    <div className="space-y-4">
      <Card>
        <CardHeader><CardTitle>{t("selectFaculty")}</CardTitle></CardHeader>
        <CardContent>
          <Select value={facultyId} onValueChange={(v) => setFacultyId(v ?? "")}>
            <SelectTrigger className="w-72"><SelectValue placeholder={t("selectFaculty")} /></SelectTrigger>
            <SelectContent>
              {faculties.map((f) => <SelectItem key={f.id} value={String(f.id)}>{f.name}</SelectItem>)}
            </SelectContent>
          </Select>
        </CardContent>
      </Card>
      <div className="grid gap-4 lg:grid-cols-3">
        <Card>
          <CardHeader><CardTitle>{te("field")}</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <div className="flex gap-2">
              <Input value={fieldName} onChange={(e) => setFieldName(e.target.value)} placeholder={t("name")} />
              <Button disabled={!facultyId} onClick={addField}>{t("addField")}</Button>
            </div>
            <ul className="space-y-2 text-sm">
              {fields.map((f) => (
                <li key={f.id} className="rounded-lg border px-3 py-2 cursor-pointer" onClick={() => setFieldId(String(f.id))}>
                  {f.name}
                </li>
              ))}
            </ul>
          </CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>{te("major")}</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <div className="flex gap-2">
              <Input value={majorName} onChange={(e) => setMajorName(e.target.value)} placeholder={t("name")} />
              <Button disabled={!fieldId} onClick={addMajor}>{t("addMajor")}</Button>
            </div>
            <ul className="space-y-2 text-sm">
              {majors.map((m) => (
                <li key={m.id} className="rounded-lg border px-3 py-2 cursor-pointer" onClick={() => setMajorId(String(m.id))}>
                  {m.name}
                </li>
              ))}
            </ul>
          </CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>{te("course")}</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <Select value={degreeId} onValueChange={(v) => setDegreeId(v ?? "")}>
              <SelectTrigger><SelectValue placeholder={t("selectDegree")} /></SelectTrigger>
              <SelectContent>
                {degrees.map((d) => <SelectItem key={d.id} value={String(d.id)}>{d.name}</SelectItem>)}
              </SelectContent>
            </Select>
            <div className="flex gap-2">
              <Input value={courseName} onChange={(e) => setCourseName(e.target.value)} placeholder={t("name")} />
              <Button disabled={!majorId} onClick={addCourse}>{t("addCourse")}</Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
