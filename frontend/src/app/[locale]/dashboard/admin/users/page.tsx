"use client"

import { useEffect, useState } from "react"
import { useTranslations } from "next-intl"
import { toast } from "sonner"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { apiFetch, apiMutate } from "@/lib/api"

type UserRow = {
  id: number
  name: string
  email: string
  is_active: boolean
  roles: string[]
}

export default function AdminUsersPage() {
  const t = useTranslations("Admin")
  const [users, setUsers] = useState<UserRow[]>([])

  async function load() {
    const res = await apiFetch<{ data: UserRow[] | { data: UserRow[] } }>("/api/v1/admin/users")
    const raw = res.data
    setUsers(Array.isArray(raw) ? raw : raw?.data ?? [])
  }

  useEffect(() => {
    load().catch(() => setUsers([]))
  }, [])

  async function setRole(userId: number, role: string) {
    await apiMutate(`/api/v1/admin/users/${userId}/role`, "POST", { role })
    toast.success(t("role"))
    await load()
  }

  async function toggle(userId: number) {
    await apiMutate(`/api/v1/admin/users/${userId}/toggle-active`, "POST")
    toast.success(t("active"))
    await load()
  }

  return (
    <Card>
      <CardHeader><CardTitle>{t("role")}</CardTitle></CardHeader>
      <CardContent className="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Email</TableHead>
              <TableHead>{t("role")}</TableHead>
              <TableHead>{t("active")}</TableHead>
              <TableHead>{t("save")}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {users.map((user) => (
              <TableRow key={user.id}>
                <TableCell>{user.name}</TableCell>
                <TableCell>{user.email}</TableCell>
                <TableCell>
                  <Select
                    value={user.roles?.[0] || "student"}
                    onValueChange={(value) => {
                      if (value) setRole(user.id, value)
                    }}
                  >
                    <SelectTrigger><SelectValue /></SelectTrigger>
                    <SelectContent>
                      <SelectItem value="student">student</SelectItem>
                      <SelectItem value="admin">admin</SelectItem>
                      <SelectItem value="owner">owner</SelectItem>
                    </SelectContent>
                  </Select>
                </TableCell>
                <TableCell>{user.is_active ? "✓" : "✗"}</TableCell>
                <TableCell>
                  <Button variant="outline" onClick={() => toggle(user.id)}>{t("active")}</Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  )
}
