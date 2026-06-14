/**
 * Estructura inspirada en portales de salud pública (p. ej. Hospital Carlos Lanfranco La Hoz).
 * Enlaces internos: rutas React. `href`: URL absoluta externa.
 * Cada ítem puede incluir `description` para el mega menú y el menú móvil.
 * @see https://www.hcllh.gob.pe/
 */
export type NavChild = {
  label: string;
  to?: string;
  href?: string;
  /** Una línea: qué verá el usuario al entrar */
  description: string;
};

export type NavGroup = {
  id: string;
  label: string;
  /** Texto introductorio en la parte superior del panel desplegable */
  groupDescription: string;
  children: NavChild[];
};

export const portalTransparenciaHref =
  import.meta.env.VITE_PORTAL_TRANSPARENCIA_URL?.trim() ||
  'https://www.transparencia.gob.pe/';

/** Textos cortos para enlaces que no tienen submenú (accesibilidad y ayuda contextual) */
export const navStandaloneHints = {
  inicio:
    'Página principal: mensaje institucional, especialidades destacadas, equipo y accesos a citas.',
  transparencia:
    'Sitio externo del Estado peruano: transparencia, presupuesto y acceso a información pública oficial.',
} as const;

export const navGroups: NavGroup[] = [
  {
    id: 'institucional',
    label: 'Institucional',
    groupDescription: 'Quiénes somos, gobernanza y documentos oficiales de la clínica.',
    children: [
      {
        label: 'Misión–Visión',
        to: '/institucional/mision-vision',
        description: 'Propósito, visión a futuro y valores que guían la atención.',
      },
      {
        label: 'Estructura orgánica',
        to: '/institucional/estructura-organica',
        description: 'Cómo está organizada la institución y las áreas principales.',
      },
      {
        label: 'Publicación de reasignación',
        to: '/institucional/publicacion-reasignacion',
        description: 'Publicaciones sobre reasignaciones cuando la norma lo exija.',
      },
      {
        label: 'Disposiciones emitidas',
        to: '/institucional/disposiciones-emitidas',
        description: 'Resoluciones, circulares y comunicados internos vigentes.',
      },
      {
        label: 'Registro de visitas',
        to: '/institucional/registro-visitas',
        description: 'Registro de visitas institucionales o de supervisión publicable.',
      },
      {
        label: 'Comité de control interno',
        to: '/institucional/comite-control-interno',
        description: 'Control interno, prevención de riesgos y mejora de la gestión.',
      },
      {
        label: 'Contáctenos',
        to: '/contacto',
        description: 'Teléfonos, correo, ubicación y formulario de contacto.',
      },
    ],
  },
  {
    id: 'organizacion',
    label: 'Organización',
    groupDescription: 'Unidades de apoyo, estadística, epidemiología, docencia y convenios.',
    children: [
      {
        label: 'Unidad de estadística, informática y telecomunicaciones',
        to: '/organizacion/uei',
        description: 'Sistemas, datos, soporte técnico y continuidad operativa.',
      },
      {
        label: 'Indicadores',
        to: '/organizacion/uei-indicadores',
        description: 'Indicadores de gestión y de salud que se publican periódicamente.',
      },
      {
        label: 'CAFAE',
        to: '/organizacion/cafae',
        description: 'Beneficios y bienestar del personal según marco aplicable.',
      },
      {
        label: 'Unidad de logística',
        to: '/organizacion/logistica',
        description: 'Abastecimiento, almacén y distribución de insumos médicos.',
      },
      {
        label: 'Epidemiología — boletines',
        to: '/organizacion/epidemiologia-boletines',
        description: 'Resúmenes de vigilancia en salud y enlaces a fuentes oficiales.',
      },
      {
        label: 'Epidemiología — salas situacionales',
        to: '/organizacion/epidemiologia-salas',
        description: 'Reportes de situación ante eventos o campañas sanitarias.',
      },
      {
        label: 'Epidemiología — documentos técnicos',
        to: '/organizacion/epidemiologia-documentos',
        description: 'Guías y notas técnicas para personal y coordinación interna.',
      },
      {
        label: 'Epidemiología — alertas',
        to: '/organizacion/epidemiologia-alertas',
        description: 'Alertas sanitarias y medidas temporales de respuesta.',
      },
      {
        label: 'Epidemiología — fichas',
        to: '/organizacion/epidemiologia-fichas',
        description: 'Información sobre fichas y notificación epidemiológica.',
      },
      {
        label: 'Salud ambiental',
        to: '/organizacion/salud-ambiental',
        description: 'Entornos saludables y prevención de riesgos ambientales.',
      },
      {
        label: 'Bioseguridad',
        to: '/organizacion/bioseguridad',
        description: 'EPI, residuos, esterilización y precauciones frente a riesgos biológicos.',
      },
      {
        label: 'Biblioteca virtual en salud (MINSA)',
        to: '/organizacion/biblioteca-minsa',
        description: 'Enlace a literatura y guías oficiales del Ministerio de Salud.',
      },
      {
        label: 'Unidad de personal – convocatorias',
        to: '/organizacion/personal-convocatorias',
        description: 'Procesos de selección y convocatorias de trabajo publicadas.',
      },
      {
        label: 'Docencia e investigación — presentación',
        to: '/organizacion/docencia-presentacion',
        description: 'Formación de profesionales de la salud e investigación ética.',
      },
      {
        label: 'Investigación — concurso caso clínico',
        to: '/organizacion/investigacion-caso-clinico',
        description: 'Bases y calendario de concursos de casos clínicos.',
      },
      {
        label: 'Docencia pregrado',
        to: '/organizacion/docencia-pregrado',
        description: 'Prácticas y rotaciones para estudiantes de carreras de salud.',
      },
      {
        label: 'Docencia posgrado',
        to: '/organizacion/docencia-posgrado',
        description: 'Especialización y educación médica continua hospitalaria.',
      },
      {
        label: 'Biblioteca virtual',
        to: '/organizacion/biblioteca-virtual',
        description: 'Recursos digitales internos y material educativo autorizado.',
      },
      {
        label: 'Convenios',
        to: '/organizacion/convenios',
        description: 'Alianzas con otras instituciones de salud y educación.',
      },
      {
        label: 'Galería',
        to: '/organizacion/galeria',
        description: 'Imágenes de actividades institucionales y campañas.',
      },
    ],
  },
  {
    id: 'prensa',
    label: 'Prensa',
    groupDescription: 'Noticias, comunicación institucional y cómo ubicarnos.',
    children: [
      {
        label: 'Boletines',
        to: '/prensa/boletines',
        description: 'Boletines periódicos con actividades y cifras destacadas.',
      },
      {
        label: 'Noticias',
        to: '/blog',
        description: 'Artículos de educación en salud y consejos para pacientes.',
      },
      {
        label: 'Nuestra ubicación',
        to: '/sedes',
        description: 'Direcciones, horarios por sede y modalidad de emergencias.',
      },
      {
        label: 'Comunicados',
        to: '/prensa/comunicados',
        description: 'Notas oficiales a medios y público en general.',
      },
    ],
  },
  {
    id: 'atencion',
    label: 'Atención al ciudadano',
    groupDescription: 'Servicios, orientación al paciente, calidad y trámites frecuentes.',
    children: [
      {
        label: 'Cartera de servicios',
        to: '/especialidades',
        description: 'Listado de especialidades y servicios ambulatorios disponibles.',
      },
      {
        label: 'Flujograma de atención',
        to: '/atencion/flujograma',
        description: 'Pasos desde la cita hasta el alta o salida del servicio.',
      },
      {
        label: 'Guía del paciente',
        to: '/manual-politicas',
        description: 'Derechos del paciente, privacidad y políticas de atención.',
      },
      {
        label: 'CONADIS',
        to: '/atencion/conadis',
        description: 'Accesibilidad y derechos de las personas con discapacidad.',
      },
      {
        label: 'Oficina de gestión de la calidad',
        to: '/atencion/calidad',
        description: 'Quejas, sugerencias, encuestas y mejora continua.',
      },
      {
        label: 'Seguro integral de salud',
        to: '/atencion/sis',
        description: 'Información orientativa sobre el SIS y enlaces oficiales.',
      },
      {
        label: 'Trámites administrativos',
        to: '/atencion/tramites',
        description: 'Certificados, facturación, resultados y otros trámites frecuentes.',
      },
      {
        label: 'Equipo médico',
        to: '/equipo',
        description: 'Directorio de médicos y especialidades vinculadas.',
      },
      {
        label: 'Agendar cita',
        to: '/cita',
        description: 'Solicitud de cita en línea con datos del paciente y motivo.',
      },
      {
        label: 'Pago en línea',
        to: '/pagar',
        description: 'Paga consultas y procedimientos con tarjeta (Stripe).',
      },
    ],
  },
];
