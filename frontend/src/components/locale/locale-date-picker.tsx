"use client"

import * as React from "react"
import { useLocale, useTranslations } from "next-intl"
import { Calendar } from "@/components/ui/calendar"
import { Button } from "@/components/ui/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { formatDate } from "@/lib/format"
import type { Locale } from "@/i18n/config"
import { faIR } from "date-fns-jalali/locale/fa-IR"
import { enUS } from "date-fns/locale/en-US"
import { CalendarIcon } from "lucide-react"
import { cn } from "@/lib/utils"

export function LocaleDatePicker({
  date,
  onChange,
}: {
  date?: Date
  onChange: (date?: Date) => void
}) {
  const locale = useLocale() as Locale
  const t = useTranslations("Common")
  const [open, setOpen] = React.useState(false)

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger
        render={
          <Button
            variant="outline"
            className={cn(
              "w-full justify-start text-start font-normal",
              !date && "text-muted-foreground",
            )}
          />
        }
      >
        <CalendarIcon className="me-2 size-4" />
        {date ? formatDate(date, locale) : t("date")}
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <Calendar
          mode="single"
          selected={date}
          onSelect={(d) => {
            onChange(d)
            setOpen(false)
          }}
          locale={locale === "fa" ? faIR : enUS}
        />
      </PopoverContent>
    </Popover>
  )
}
