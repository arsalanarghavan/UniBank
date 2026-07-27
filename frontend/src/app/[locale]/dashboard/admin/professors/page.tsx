"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { apiFetch, apiMutate } from "@/lib/api"

type Professor = {
  id: number
  name: string
  bio?: string
  links?: { id: number; type: string; title?: string; url: string }[]
  teaching_assignments?: { id: number; university?: { name: string }; course?: { name: string } }[]
}

export default function ProfessorsAdminPage() {
  const t = useTranslations("Admin")
  const [items, setItems] = useState<Professor[]>([])
  const [selected, setSelected] = useState<Professor | null>(null)
  const [name, setName] = useState("")
  const [bio, setBio] = useState("")
  const [linkType, setLinkType] = useState("google_scholar")
  const [linkUrl, setLinkUrl] = useState("")
  const [unis, setUnis] = useState<{ id: number; name: string }[]>([])
  const [courses, setCourses] = useState<{ id: number; name: string }[]>([])
  const [uniId, setUniId] = useState("")
  const [courseId, setCourseId] = useState("")

  async function reload() {
    const p = await apiFetch<{ data: Professor[] | { data: Professor[] } }>("/api/v1/professors?per_page=100")
    const raw = p.data
    setItems(Array.isArray(raw) ? raw : raw?.data ?? [])
    const u = await apiFetch<{ data: { id: number; name: string }[] }>("/api/v1/universities?all=1")
    setUnis(u.data)
  }

  useEffect(() => { reload().catch(() => undefined) }, [])

  async function openProfessor(id: number) {
    const res = await apiFetch<{ data: Professor }>(`/api/v1/professors/${id}`)
    setSelected(res.data)
  }

  async function addProfessor() {
    await apiMutate("/api/v1/admin/professors", "POST", { name, bio: bio || null })
    setName(""); setBio("")
    toast.success(t("addProfessor"))
    await reload()
  }

  async function addLink() {
    if (!selected) return
    await apiMutate(`/api/v1/admin/professors/${selected.id}/links`, "POST", { type: linkType, url: linkUrl })
    setLinkUrl("")
    toast.success(t("addLink"))
    await openProfessor(selected.id)
  }

  async function addAssignment() {
    if (!selected) return
    await apiMutate(`/api/v1/admin/professors/${selected.id}/assignments`, "POST", {
      university_id: Number(uniId),
      course_id: Number(courseId),
    })
    toast.success(t("addAssignment"))
    await openProfessor(selected.id)
  }

  useEffect(() => {
    // load courses loosely via fields of all faculties
    apiFetch<{ data: { majors?: { courses?: { id: number; name: string }[] }[] }[] }>("/api/v1/fields")
      .then((r) => {
        const list: { id: number; name: string }[] = []
        for (const f of r.data) {
          for (const m of f.majors || []) {
            for (const c of m.courses || []) list.push(c)
          }
        }
        setCourses(list)
      })
      .catch(() => setCourses([]))
  }, [])

  return (
    <div className="grid gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader><CardTitle>{t("addProfessor")}</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <Input value={name} onChange={(e) => setName(e.target.value)} placeholder={t("name")} />
          <Textarea value={bio} onChange={(e) => setBio(e.target.value)} placeholder={t("bio")} />
          <Button onClick={addProfessor}>{t("save")}</Button>
          <ul className="space-y-2 text-sm">
            {items.map((p) => (
              <li key={p.id} className="rounded-lg border px-3 py-2 cursor-pointer" onClick={() => openProfessor(p.id)}>
                {p.name}
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>
      {selected && (
        <Card>
          <CardHeader><CardTitle>{selected.name}</CardTitle></CardHeader>
          <CardContent className="space-y-4">
            <div>
              <div className="mb-2 font-medium">{t("links")}</div>
              <div className="flex flex-wrap gap-2">
                <Select value={linkType} onValueChange={(v) => setLinkType(v ?? "")}>
                  <SelectTrigger className="w-40"><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {["google_scholar","orcid","researchgate","website","book","other"].map((x) => (
                      <SelectItem key={x} value={x}>{x}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <Input value={linkUrl} onChange={(e) => setLinkUrl(e.target.value)} placeholder={t("linkUrl")} />
                <Button onClick={addLink}>{t("addLink")}</Button>
              </div>
              <ul className="mt-2 space-y-1 text-sm">
                {(selected.links || []).map((l) => (
                  <li key={l.id}><a className="underline" href={l.url} target="_blank" rel="noreferrer">{l.type}: {l.url}</a></li>
                ))}
              </ul>
            </div>
            <div>
              <div className="mb-2 font-medium">{t("assignments")}</div>
              <div className="flex flex-wrap gap-2">
                <Select value={uniId} onValueChange={(v) => setUniId(v ?? "")}>
                  <SelectTrigger className="w-40"><SelectValue placeholder={t("selectUniversity")} /></SelectTrigger>
                  <SelectContent>
                    {unis.map((u) => <SelectItem key={u.id} value={String(u.id)}>{u.name}</SelectItem>)}
                  </SelectContent>
                </Select>
                <Select value={courseId} onValueChange={(v) => setCourseId(v ?? "")}>
                  <SelectTrigger className="w-40"><SelectValue placeholder={t("name")} /></SelectTrigger>
                  <SelectContent>
                    {courses.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                  </SelectContent>
                </Select>
                <Button onClick={addAssignment}>{t("addAssignment")}</Button>
              </div>
              <ul className="mt-2 space-y-1 text-sm">
                {(selected.teaching_assignments || []).map((a) => (
                  <li key={a.id}>{a.university?.name} · {a.course?.name}</li>
                ))}
              </ul>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  )
}
