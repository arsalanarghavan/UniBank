"use client";

import * as React from "react";
import { useLocale, useTranslations } from "next-intl";
import {
  BookOpenIcon,
  GraduationCapIcon,
  LayoutDashboardIcon,
  ListChecksIcon,
  SearchIcon,
  ShieldCheckIcon,
  TrophyIcon,
} from "lucide-react";
import { NavMain } from "@/components/nav-main";
import { NavUser } from "@/components/nav-user";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarRail,
} from "@/components/ui/sidebar";
import { apiFetch, apiMutate } from "@/lib/api";
import { useRouter } from "next/navigation";

type MeResponse = {
  data: {
    id: number;
    name: string;
    email: string;
    roles: string[];
  };
};

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
  const t = useTranslations("Nav");
  const tApp = useTranslations("App");
  const tAuth = useTranslations("Auth");
  const locale = useLocale();
  const router = useRouter();
  const [user, setUser] = React.useState({
    name: "...",
    email: "",
    avatar: "",
  });
  const [roles, setRoles] = React.useState<string[]>([]);

  React.useEffect(() => {
    apiFetch<MeResponse>("/api/v1/auth/me")
      .then((res) => {
        setUser({
          name: res.data.name,
          email: res.data.email,
          avatar: "",
        });
        setRoles(res.data.roles || []);
      })
      .catch(() => {
        router.replace(`/${locale}/login`);
      });
  }, [locale, router]);

  const isAdmin = roles.includes("admin") || roles.includes("owner");
  const base = `/${locale}/dashboard`;

  const navMain = [
    {
      title: t("dashboard"),
      url: base,
      icon: <LayoutDashboardIcon />,
      isActive: true,
      items: [],
    },
    {
      title: t("submit"),
      url: `${base}/submit`,
      icon: <BookOpenIcon />,
      items: [],
    },
    {
      title: t("myExperiences"),
      url: `${base}/experiences`,
      icon: <ListChecksIcon />,
      items: [],
    },
    {
      title: t("search"),
      url: `${base}/search`,
      icon: <SearchIcon />,
      items: [],
    },
    {
      title: t("rankings"),
      url: `${base}/rankings`,
      icon: <TrophyIcon />,
      items: [],
    },
    {
      title: t("rules"),
      url: `${base}/rules`,
      icon: <GraduationCapIcon />,
      items: [],
    },
    ...(isAdmin
      ? [
          {
            title: t("admin"),
            url: `${base}/admin/stats`,
            icon: <ShieldCheckIcon />,
            items: [
              { title: t("stats"), url: `${base}/admin/stats` },
              { title: t("moderation"), url: `${base}/admin/moderation` },
              { title: t("universityCategories"), url: `${base}/admin/university-categories` },
              { title: t("universities"), url: `${base}/admin/universities` },
              { title: t("faculties"), url: `${base}/admin/faculties` },
              { title: t("degreeLevels"), url: `${base}/admin/degree-levels` },
              { title: t("taxonomy"), url: `${base}/admin/taxonomy` },
              { title: t("professorsAdmin"), url: `${base}/admin/professors` },
              { title: t("bots"), url: `${base}/admin/bots` },
              { title: t("users"), url: `${base}/admin/users` },
              { title: t("settings"), url: `${base}/admin/settings` },
              { title: t("broadcast"), url: `${base}/admin/broadcast` },
            ],
          },
        ]
      : []),
  ];

  async function logout() {
    try {
      await apiMutate("/api/v1/auth/logout", "POST");
    } finally {
      window.localStorage.removeItem("ostadbank_token");
      router.replace(`/${locale}/login`);
    }
  }

  return (
    <Sidebar collapsible="icon" {...props}>
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" render={<a href={base} />}>
                <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                  <GraduationCapIcon className="size-4" />
                </div>
                <div className="grid flex-1 text-start text-sm leading-tight">
                  <span className="truncate font-medium">{tApp("name")}</span>
                  <span className="truncate text-xs">{tApp("tagline")}</span>
                </div>
              </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={navMain} />
      </SidebarContent>
      <SidebarFooter>
        <NavUser
          user={user}
          onLogout={logout}
          logoutLabel={tAuth("logout")}
        />
      </SidebarFooter>
      <SidebarRail />
    </Sidebar>
  );
}
