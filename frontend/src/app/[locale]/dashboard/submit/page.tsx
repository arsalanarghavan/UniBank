"use client"

import { useEffect, useMemo, useState } from "react"
import { useLocale, useTranslations } from "next-intl"
import { useRouter } from "next/navigation"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import { Field, FieldGroup, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { apiFetch, apiMutate, apiUpload } from "@/lib/api"
import { LocaleDatePicker } from "@/components/locale/locale-date-picker"

type FieldNode = {
  id: number
  name: string
  majors?: { id: number; name: string; courses?: { id: number; name: string; degree_level_id?: number }[] }[]
}

export default function SubmitExperiencePage() {
  const t = useTranslations("Experiences")
  const tCommon = useTranslations("Common")
  const locale = useLocale()
  const router = useRouter()

  const [categories, setCategories] = useState<{ id: number; name: string }[]>([])
  const [universities, setUniversities] = useState<{ id: number; name: string }[]>([])
  const [faculties, setFaculties] = useState<{ id: number; name: string }[]>([])
  const [degrees, setDegrees] = useState<{ id: number; name: string }[]>([])
  const [fields, setFields] = useState<FieldNode[]>([])
  const [professors, setProfessors] = useState<{ id: number; name: string }[]>([])

  const [categoryId, setCategoryId] = useState("")
  const [universityId, setUniversityId] = useState("")
  const [facultyId, setFacultyId] = useState("")
  const [fieldId, setFieldId] = useState("")
  const [majorId, setMajorId] = useState("")
  const [degreeId, setDegreeId] = useState("")
  const [courseId, setCourseId] = useState("")
  const [professorId, setProfessorId] = useState("")
  const [teachingStyle, setTeachingStyle] = useState("")
  const [teachingType, setTeachingType] = useState("in_person")
  const [notes, setNotes] = useState("")
  const [project, setProject] = useState("")
  const [exam, setExam] = useState("")
  const [conclusion, setConclusion] = useState("")
  const [contactMethods, setContactMethods] = useState("")
  const [attendanceRequired, setAttendanceRequired] = useState(false)
  const [attendanceDetails, setAttendanceDetails] = useState("")
  const [teachingRating, setTeachingRating] = useState("good")
  const [examDifficulty, setExamDifficulty] = useState("medium")
  const [overallRating, setOverallRating] = useState("4")
  const [hasNotes, setHasNotes] = useState(false)
  const [hasProject, setHasProject] = useState(false)
  const [hasExam, setHasExam] = useState(false)
  const [experienceDate, setExperienceDate] = useState<Date | undefined>(new Date())
  const [file, setFile] = useState<File | null>(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    apiFetch<{ data: { id: number; name: string }[] }>("/api/v1/university-categories")
      .then((r) => setCategories(r.data)).catch(() => setCategories([]))
    apiFetch<{ data: { id: number; name: string }[] }>("/api/v1/degree-levels")
      .then((r) => setDegrees(r.data)).catch(() => setDegrees([]))
  }, [])

  useEffect(() => {
    const q = categoryId ? `?university_category_id=${categoryId}` : ""
    apiFetch<{ data: { id: number; name: string }[] }>(`/api/v1/universities${q}`)
      .then((r) => setUniversities(r.data)).catch(() => setUniversities([]))
  }, [categoryId])

  useEffect(() => {
    if (!universityId) { setFaculties([]); return }
    apiFetch<{ data: { id: number; name: string }[] }>(`/api/v1/faculties?university_id=${universityId}`)
      .then((r) => setFaculties(r.data)).catch(() => setFaculties([]))
  }, [universityId])

  useEffect(() => {
    if (!facultyId) { setFields([]); return }
    apiFetch<{ data: FieldNode[] }>(`/api/v1/fields?faculty_id=${facultyId}`)
      .then((r) => setFields(r.data)).catch(() => setFields([]))
  }, [facultyId])

  useEffect(() => {
    if (!universityId || !courseId) {
      setProfessors([])
      return
    }
    apiFetch<{ data: { id: number; name: string }[] | { data: { id: number; name: string }[] } }>(
      `/api/v1/professors?university_id=${universityId}&course_id=${courseId}&per_page=100`,
    ).then((r) => {
      const raw = r.data
      setProfessors(Array.isArray(raw) ? raw : raw?.data ?? [])
    }).catch(() => setProfessors([]))
  }, [universityId, courseId])

  const majors = useMemo(() => fields.find((f) => String(f.id) === fieldId)?.majors || [], [fields, fieldId])
  const courses = useMemo(() => {
    const all = majors.find((m) => String(m.id) === majorId)?.courses || []
    if (!degreeId) return all
    return all.filter((c) => !c.degree_level_id || String(c.degree_level_id) === degreeId)
  }, [majors, majorId, degreeId])

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    try {
      const created = await apiMutate<{ data: { id: number } }>("/api/v1/experiences", "POST", {
        university_id: Number(universityId),
        faculty_id: Number(facultyId),
        field_id: Number(fieldId),
        major_id: Number(majorId),
        course_id: Number(courseId),
        degree_level_id: Number(degreeId),
        professor_id: Number(professorId),
        teaching_style: teachingStyle,
        teaching_type: teachingType,
        notes,
        project,
        exam,
        conclusion,
        contact_methods: contactMethods
          ? contactMethods.split(",").map((s) => s.trim()).filter(Boolean)
          : [],
        attendance_required: attendanceRequired,
        attendance_details: attendanceDetails,
        teaching_rating: teachingRating,
        exam_difficulty: examDifficulty,
        overall_rating: Number(overallRating),
        has_notes: hasNotes,
        has_project: hasProject,
        has_exam: hasExam,
      })
      if (file && created.data?.id) {
        const fd = new FormData()
        fd.append("file", file)
        await apiUpload(`/api/v1/experiences/${created.data.id}/attachments`, fd)
      }
      toast.success(t("created"))
      router.push(`/${locale}/dashboard/experiences`)
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Error")
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="mx-auto flex w-full max-w-3xl flex-col gap-4">
      <h1 className="text-2xl font-bold">{t("submitTitle")}</h1>
      <Card>
        <CardHeader>
          <CardTitle>{t("submitTitle")}</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={onSubmit} className="space-y-4">
            <FieldGroup className="grid gap-4 md:grid-cols-2">
              <Field>
                <FieldLabel>{t("category")}</FieldLabel>
                <Select value={categoryId} onValueChange={(v) => { setCategoryId(v ?? ""); setUniversityId(""); setFacultyId("") }}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {categories.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("university")}</FieldLabel>
                <Select value={universityId} onValueChange={(v) => { setUniversityId(v ?? ""); setFacultyId(""); setFieldId(""); setMajorId(""); setCourseId(""); setProfessorId("") }}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {universities.map((u) => <SelectItem key={u.id} value={String(u.id)}>{u.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("faculty")}</FieldLabel>
                <Select value={facultyId} onValueChange={(v) => { setFacultyId(v ?? ""); setFieldId(""); setMajorId(""); setCourseId("") }}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {faculties.map((f) => <SelectItem key={f.id} value={String(f.id)}>{f.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("field")}</FieldLabel>
                <Select value={fieldId} onValueChange={(v) => { setFieldId(v ?? ""); setMajorId(""); setCourseId("") }}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {fields.map((f) => <SelectItem key={f.id} value={String(f.id)}>{f.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("major")}</FieldLabel>
                <Select value={majorId} onValueChange={(v) => { setMajorId(v ?? ""); setCourseId("") }}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {majors.map((m) => <SelectItem key={m.id} value={String(m.id)}>{m.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("degreeLevel")}</FieldLabel>
                <Select value={degreeId} onValueChange={(v) => { setDegreeId(v ?? ""); setCourseId("") }}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {degrees.map((d) => <SelectItem key={d.id} value={String(d.id)}>{d.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("course")}</FieldLabel>
                <Select value={courseId} onValueChange={(v) => { setCourseId(v ?? ""); setProfessorId("") }}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {courses.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("professor")}</FieldLabel>
                <Select value={professorId} onValueChange={(v) => setProfessorId(v ?? "")}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {professors.map((p) => <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
            </FieldGroup>

            <Field>
              <FieldLabel>{t("teachingType")}</FieldLabel>
              <Select value={teachingType} onValueChange={(v) => setTeachingType(v ?? "in_person")}>
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="in_person">{t("inPerson")}</SelectItem>
                  <SelectItem value="online">{t("online")}</SelectItem>
                  <SelectItem value="hybrid">{t("hybrid")}</SelectItem>
                </SelectContent>
              </Select>
            </Field>

            <Field>
              <FieldLabel>{t("teachingStyle")}</FieldLabel>
              <Textarea value={teachingStyle} onChange={(e) => setTeachingStyle(e.target.value)} required />
            </Field>
            <Field>
              <FieldLabel>{t("conclusion")}</FieldLabel>
              <Textarea value={conclusion} onChange={(e) => setConclusion(e.target.value)} required />
            </Field>
            <Field>
              <FieldLabel>{t("notes")}</FieldLabel>
              <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} />
            </Field>
            <Field>
              <FieldLabel>{t("attachments")}</FieldLabel>
              <Input type="file" onChange={(e) => setFile(e.target.files?.[0] || null)} />
            </Field>
            <Field>
              <FieldLabel>{t("project")}</FieldLabel>
              <Textarea value={project} onChange={(e) => setProject(e.target.value)} />
            </Field>
            <Field>
              <FieldLabel>{t("exam")}</FieldLabel>
              <Textarea value={exam} onChange={(e) => setExam(e.target.value)} />
            </Field>
            <Field>
              <FieldLabel>{t("contactMethods")}</FieldLabel>
              <Input value={contactMethods} onChange={(e) => setContactMethods(e.target.value)} placeholder="telegram, email" />
            </Field>
            <Field>
              <FieldLabel>{t("attendanceDetails")}</FieldLabel>
              <Input value={attendanceDetails} onChange={(e) => setAttendanceDetails(e.target.value)} />
            </Field>

            <div className="grid gap-4 md:grid-cols-3">
              <Field>
                <FieldLabel>{t("teachingRating")}</FieldLabel>
                <Select value={teachingRating} onValueChange={(v) => setTeachingRating(v ?? "good")}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="excellent">{t("excellent")}</SelectItem>
                    <SelectItem value="good">{t("good")}</SelectItem>
                    <SelectItem value="average">{t("average")}</SelectItem>
                    <SelectItem value="poor">{t("poor")}</SelectItem>
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("examDifficulty")}</FieldLabel>
                <Select value={examDifficulty} onValueChange={(v) => setExamDifficulty(v ?? "medium")}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="easy">{t("easy")}</SelectItem>
                    <SelectItem value="medium">{t("medium")}</SelectItem>
                    <SelectItem value="hard">{t("hard")}</SelectItem>
                  </SelectContent>
                </Select>
              </Field>
              <Field>
                <FieldLabel>{t("overallRating")}</FieldLabel>
                <Select value={overallRating} onValueChange={(v) => setOverallRating(v ?? "4")}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {[1,2,3,4,5].map((n) => <SelectItem key={n} value={String(n)}>{n}</SelectItem>)}
                  </SelectContent>
                </Select>
              </Field>
            </div>

            <Field>
              <FieldLabel>{tCommon("date")}</FieldLabel>
              <LocaleDatePicker date={experienceDate} onChange={setExperienceDate} />
            </Field>

            <div className="flex flex-wrap gap-4">
              <label className="flex items-center gap-2 text-sm">
                <Checkbox checked={attendanceRequired} onCheckedChange={(v) => setAttendanceRequired(Boolean(v))} />
                {t("attendanceRequired")}
              </label>
              <label className="flex items-center gap-2 text-sm">
                <Checkbox checked={hasNotes} onCheckedChange={(v) => setHasNotes(Boolean(v))} />
                {t("hasNotes")}
              </label>
              <label className="flex items-center gap-2 text-sm">
                <Checkbox checked={hasProject} onCheckedChange={(v) => setHasProject(Boolean(v))} />
                {t("hasProject")}
              </label>
              <label className="flex items-center gap-2 text-sm">
                <Checkbox checked={hasExam} onCheckedChange={(v) => setHasExam(Boolean(v))} />
                {t("hasExam")}
              </label>
            </div>

            <Button type="submit" disabled={loading}>{t("submit")}</Button>
          </form>
        </CardContent>
      </Card>
    </div>
  )
}
