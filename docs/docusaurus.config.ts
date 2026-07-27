import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

const config: Config = {
  title: 'OstadBank API',
  tagline: 'Professor Archive API documentation',
  favicon: 'img/favicon.ico',
  future: {
    v4: true,
  },
  url: 'https://docs.ostadbank.local',
  baseUrl: '/',
  organizationName: 'arsalanarghavan',
  projectName: 'UniBank',
  onBrokenLinks: 'throw',
  i18n: {
    defaultLocale: 'en',
    locales: ['en', 'fa'],
  },
  presets: [
    [
      'classic',
      {
        docs: {
          sidebarPath: './sidebars.ts',
          routeBasePath: '/',
        },
        blog: false,
        theme: {
          customCss: './src/css/custom.css',
        },
      } satisfies Preset.Options,
    ],
  ],
  themeConfig: {
    image: 'img/docusaurus-social-card.jpg',
    colorMode: {
      respectPrefersColorScheme: true,
    },
    navbar: {
      title: 'OstadBank Docs',
      logo: {
        alt: 'OstadBank',
        src: 'img/logo.svg',
      },
      items: [
        {
          type: 'docSidebar',
          sidebarId: 'tutorialSidebar',
          position: 'left',
          label: 'API',
        },
        {
          href: 'http://localhost:8000/docs/api',
          label: 'OpenAPI',
          position: 'right',
        },
        {
          href: 'https://github.com/arsalanarghavan/UniBank',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Docs',
          items: [
            {label: 'Introduction', to: '/'},
            {label: 'Errors', to: '/errors'},
            {label: 'Deployment', to: '/deployment'},
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} OstadBank.`,
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.dracula,
    },
  } satisfies Preset.ThemeConfig,
};

export default config;
