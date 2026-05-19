import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

const LIGHT: Record<string, string> = {
  primary:   '#1565C0',
  secondary: '#FF6F00',
  error:     '#C62828',
  success:   '#2E7D32',
  warning:   '#F57F17',
  info:      '#01579B',
  surface:   '#FFFFFF',
  background:'#F5F7FA',
}

const DARK: Record<string, string> = {
  primary:   '#1E88E5',
  secondary: '#FFA000',
  error:     '#EF5350',
  success:   '#66BB6A',
  warning:   '#FFB300',
  info:      '#29B6F6',
  surface:   '#1E1E2E',
  background:'#121218',
}

export const vuetify = createVuetify({
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        dark: false,
        colors: LIGHT,
      },
      dark: {
        dark: true,
        colors: DARK,
      },
    },
  },

  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },

  defaults: {
    VTextField: {
      density:  'compact',
      variant:  'outlined',
      hideDetails: 'auto',
    },
    VSelect: {
      density:  'compact',
      variant:  'outlined',
      hideDetails: 'auto',
    },
    VAutocomplete: {
      density:  'compact',
      variant:  'outlined',
      hideDetails: 'auto',
    },
    VTextarea: {
      density:  'compact',
      variant:  'outlined',
      hideDetails: 'auto',
    },
    VBtn: {
      density: 'comfortable',
      style:   'text-transform: none; letter-spacing: normal;',
    },
    VCard: {
      rounded: 'lg',
    },
    VChip: {
      rounded: 'md',
    },
  },
})

export default vuetify