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
import { apiFetch, apiMutate } from "@/lib/api"
import { LocaleDatePicker } from "@/components/locale/locale-date-picker"

type FieldNode = {
  id: number
  name: string
  majors?: { id: number; name: string; courses?: { id: number; name: string }[] }[]
}

export default function SubmitExperiencePage() {
  const t = useTranslations("Experiences")
  const tCommon = useTranslations("Common")
  const locale = useLocale()
  const router = useRouter()
  const [fields, setFields] = useState<FieldNode[]>([])
  const [professors, setProfessors] = useState<{ id: number; name: string }[]>([])
  const [fieldId, setFieldId] = useState("")
  const [majorId, setMajorId] = useState("")
  const [courseId, setCourseId] = useState("")
  const [professorId, setProfessorId] = useState("")
  const [teachingStyle, setTeachingStyle] = useState("")
  const [notes, setNotes] = useState("")
  const [project, setProject] = useState("")
  const [exam, setExam] = useState("")
  const [conclusion, setConclusion] = useState("")
  const [attendanceRequired, setAttendanceRequired] = useState(false)
  const [attendanceDetails, setAttendanceDetails] = useState("")
  const [teachingRating, setTeachingRating] = useState("good")
  const [examDifficulty, setExamDifficulty] = useState("medium")
  const [overallRating, setOverallRating] = useState("4")
  const [hasNotes, setHasNotes] = useState(false)
  const [hasProject, setHasProject] = useState(false)
  const [hasExam, setHasExam] = useState(false)
  const [experienceDate, setExperienceDate] = useState<Date | undefined>(new Date())
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    apiFetch<{ data: FieldNode[] }>("/api/v1/fields").then((r) => setFields(r.data)).catch(() => setFields([]))
    apiFetch<{ data: { id: number; name: string }[] | { data: { id: number; name: string }[] } }>("/api/v1/professors").then((r) => {
      const raw = r.data
      setProfessors(Array.isArray(raw) ? raw : raw?.data ?? [])
    }).catch(() => setProfessors([]))
  }, [])

  const majors = useMemo(() => fields.find((f) => String(f.id) === fieldId)?.majors || [], [fields, fieldId])
  const courses = useMemo(() => majors.find((m) => String(m.id) === majorId)?.courses || [], [majors, majorId])

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    try {
      await apiMutate("/api/v1/experiences", "POST", {
        field_id: Number(fieldId),
        major_id: Number(majorId),
        course_id: Number(courseId),
        professor_id: Number(professorId),
        teaching_style: teachingStyle,
        notes,
        project,
        exam,
        conclusion,
        attendance_required: attendanceRequired,
        attendance_details: attendanceDetails,
        teaching_rating: teachingRating,
        exam_difficulty: examDifficulty,
        overall_rating: Number(overallRating),
        has_notes: hasNotes,
        has_project: hasProject,
        has_exam: hasExam,
      })
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
                <FieldLabel>{t("course")}</FieldLabel>
                <Select value={courseId} onValueChange={(v) => setCourseId(v ?? "")}>
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
              <FieldLabel>{t("project")}</FieldLabel>
              <Textarea value={project} onChange={(e) => setProject(e.target.value)} />
            </Field>
            <Field>
              <FieldLabel>{t("exam")}</FieldLabel>
              <Textarea value={exam} onChange={(e) => setExam(e.target.value)} />
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
