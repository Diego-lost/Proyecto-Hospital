/** Contenido de portal institucional (sustituible luego por CMS / API). */

export type PortalLink = { label: string; href: string };

export type PortalDoc = {
  title: string;
  intro: string;
  body?: string[];
  bullets?: string[];
  links?: PortalLink[];
};

function page(
  title: string,
  intro: string,
  extra?: Omit<PortalDoc, 'title' | 'intro'>,
): PortalDoc {
  return { title, intro, ...extra };
}

export const institucionalPages: Record<string, PortalDoc> = {
  'mision-vision': page(
    'Misión y visión',
    'Declaración de propósito y dirección estratégica de Clínica NovaSalud.',
    {
      body: [
        'Nuestra misión es brindar atención integral en salud, con estándares de calidad, seguridad del paciente y calidez humana, articulando prevención, diagnóstico y tratamiento con tecnología clínica y un equipo multidisciplinario comprometido.',
        'Nuestra visión es ser reconocidos como una institución de salud de referencia en la región, por resultados clínicos medibles, innovación responsable y confianza de la comunidad.',
      ],
      bullets: [
        'Valores: ética profesional, respeto a la dignidad de la persona, trabajo en equipo y mejora continua.',
        'Alineación con normativa nacional aplicable a establecimientos de salud y protección de datos personales.',
      ],
    },
  ),
  'estructura-organica': page(
    'Estructura orgánica',
    'Organización funcional de la clínica para transparencia y orientación al usuario.',
    {
      body: [
        'La dirección general coordina las áreas asistenciales y de apoyo. Las unidades clínicas (consulta externa, hospitalización, centro quirúrgico, imagen, laboratorio, farmacia) operan bajo protocolos unificados de calidad y bioseguridad.',
        'Las áreas de apoyo incluyen gestión de personas, logística, estadística e informática, y oficinas de atención al usuario y calidad.',
      ],
      bullets: [
        'Dirección médica: lineamientos clínicos, auditoría médica y seguridad del paciente.',
        'Enfermería: cuidados transversales y educación en salud.',
        'Gestión de calidad: indicadores, quejas y sugerencias, acreditación y mejora continua.',
      ],
    },
  ),
  'publicacion-reasignacion': page(
    'Publicación de reasignación',
    'Espacio para publicar, cuando corresponda, información sobre reasignaciones presupuestarias o de plazas según disposiciones legales y políticas internas.',
    {
      body: [
        'Las reasignaciones se documentan conforme a la normativa aplicable y a los acuerdos del directorio o autoridad competente. Los documentos aprobados se publican aquí o en el portal de transparencia que corresponda a la naturaleza jurídica de la institución.',
        'Si no existen actuaciones vigentes en un periodo, esta sección permanece sin registros nuevos.',
      ],
    },
  ),
  'disposiciones-emitidas': page(
    'Disposiciones emitidas',
    'Resoluciones, circulares internas y comunicados oficiales de la administración.',
    {
      body: [
        'Aquí se centralizan las disposiciones de carácter general que orientan al personal y a los usuarios sobre horarios, procedimientos, uso de canales digitales y medidas extraordinarias (p. ej. contingencia sanitaria).',
        'Los documentos pueden publicarse en formato PDF con fecha, número de documento y área emisora.',
      ],
      bullets: [
        'Ejemplos: directiva de atención en feriados, protocolo de visitas, lineamiento de teleconsulta.',
      ],
    },
  ),
  'registro-visitas': page(
    'Registro de visitas',
    'Información sobre visitas institucionales, supervisión o comisiones, según política de transparencia.',
    {
      body: [
        'Se registra la fecha, entidad visitante, motivo y conclusiones no confidenciales cuando la visita sea de interés público o forme parte de programas de supervisión.',
        'Los datos personales de visitantes se tratan conforme a la Ley N.º 29733 y su reglamento.',
      ],
    },
  ),
  'comite-control-interno': page(
    'Comité de control interno',
    'El control interno promueve el uso eficiente de recursos y el cumplimiento de normas en la gestión institucional.',
    {
      body: [
        'El comité de control interno (o la figura equivalente según el marco legal aplicable a su tipo de entidad) apoya la prevención de riesgos, la evaluación de controles y el seguimiento de planes correctivos.',
        'Los informes de gestión y planes anuales pueden enlazarse desde esta sección una vez aprobados.',
      ],
      links: [{ label: 'Portal institucional de transparencia (referencia nacional)', href: 'https://www.transparencia.gob.pe/' }],
    },
  ),
};

export const organizacionPages: Record<string, PortalDoc> = {
  uei: page(
    'Unidad de estadística, informática y telecomunicaciones',
    'Gestión de datos, sistemas de información y conectividad para apoyar la decisión clínica y administrativa.',
    {
      body: [
        'La unidad asegura la disponibilidad de los sistemas clínicos y administrativos, respaldos, seguridad básica de la información y soporte a usuarios internos.',
        'Coordina la generación de reportes estadísticos respetando la confidencialidad de la historia clínica.',
      ],
    },
  ),
  'uei-indicadores': page(
    'Indicadores',
    'Indicadores de gestión y de salud que la institución decide hacer públicos.',
    {
      body: [
        'Los indicadores permiten medir accesibilidad, tiempos de espera, infecciones asociados a la atención, satisfacción del usuario u otros según el plan estratégico.',
        'La publicación periódica (trimestral o anual) favorece la rendición de cuentas y la mejora continua.',
      ],
      bullets: [
        'Ejemplos: número de atenciones por especialidad, ocupación de camas, tiempos promedio de espera.',
      ],
    },
  ),
  cafae: page(
    'CAFAE',
    'Comité de administración del fondo de asistencia y estímulo del personal — cuando aplique al régimen laboral de la institución.',
    {
      body: [
        'El CAFAE administra beneficios sociales del personal (recreación, capacitación complementaria u otros según estatuto interno), con sujeción a normas de control y transparencia.',
        'Las convocatorias y balances informativos pueden publicarse aquí para conocimiento del personal.',
      ],
    },
  ),
  logistica: page(
    'Unidad de logística',
    'Abastecimiento, almacén, distribución de insumos y apoyo a continuidad operativa.',
    {
      body: [
        'Garantiza el suministro oportuno de medicamentos, dispositivos médicos y material de curación para las unidades asistenciales, bajo criterios de cadena de frío y buenas prácticas de almacenamiento.',
        'Coordina inventarios, recepción de bienes y disposición de residuos según normativa.',
      ],
    },
  ),
  'epidemiologia-boletines': page(
    'Boletines epidemiológicos',
    'Difusión de información epidemiológica relevante para el personal y la comunidad.',
    {
      body: [
        'Los boletines resumen situaciones de vigilancia en salud pública de interés local o nacional, sin sustituir las fuentes oficiales del MINSA o DIRESA.',
      ],
      links: [{ label: 'Centro Nacional de Epidemiología, Prevención y Control de Enfermedades (CDC MINSA)', href: 'https://www.dge.gob.pe/' }],
    },
  ),
  'epidemiologia-salas': page(
    'Salas situacionales',
    'Espacio para reportes de situación en salud pública y coordinación interna ante eventos.',
    {
      body: [
        'Las salas situacionales permiten concentrar datos operativos durante brotes, campañas de vacunación o contingencias, y comunicar lineamientos internos de bioseguridad y flujo de pacientes.',
      ],
    },
  ),
  'epidemiologia-documentos': page(
    'Documentos técnicos',
    'Guías, protocolos y notas técnicas de apoyo a la práctica clínica y vigilancia.',
    {
      body: [
        'Se publican documentos elaborados o adoptados por la institución para homogeneizar criterios diagnósticos y de notificación obligatoria, en coherencia con normativa nacional.',
      ],
    },
  ),
  'epidemiologia-alertas': page(
    'Alertas epidemiológicas',
    'Comunicación oportuna de alertas sanitarias y medidas de respuesta institucional.',
    {
      body: [
        'Las alertas se activan ante eventos que requieran intensificación de vigilancia o cambios temporales en flujos de atención. Se indica fecha de inicio, nivel de alerta y recomendaciones al usuario.',
      ],
    },
  ),
  'epidemiologia-fichas': page(
    'Fichas epidemiológicas',
    'Referencia sobre fichas y notificación según eventos de vigilancia.',
    {
      body: [
        'El personal de salud utiliza fichas estandarizadas para la notificación a la autoridad de salud. Esta sección puede enlazar a formatos oficiales y recordatorios de plazos de notificación.',
      ],
    },
  ),
  'salud-ambiental': page(
    'Salud ambiental',
    'Promoción de entornos saludables y prevención de riesgos ambientales en coordinación con la comunidad.',
    {
      body: [
        'Incluye lineamientos sobre calidad del aire en instalaciones, agua segura, manejo de residuos sólidos y educación para la prevención de enfermedades relacionadas con el entorno.',
      ],
    },
  ),
  bioseguridad: page(
    'Bioseguridad',
    'Medidas para proteger al paciente, al personal y a la comunidad frente a riesgos biológicos.',
    {
      body: [
        'Uso de equipos de protección personal, segregación de residuos, esterilización, lavado de manos y precauciones estándar y por transmisión son pilares del programa de bioseguridad.',
        'Se capacita periódicamente al personal y se audit el cumplimiento de protocolos.',
      ],
    },
  ),
  'biblioteca-minsa': page(
    'Biblioteca virtual en salud (MINSA)',
    'Acceso a literatura científica y guías oficiales en salud.',
    {
      body: [
        'La Biblioteca Virtual en Salud del MINSA concentra documentos técnicos, normas y recursos educativos de acceso público. Recomendamos usarla como referencia para guías de práctica clínica y material educativo para pacientes.',
      ],
      links: [{ label: 'Biblioteca Virtual en Salud — MINSA', href: 'https://www.bvs.minsa.gob.pe/' }],
    },
  ),
  'personal-convocatorias': page(
    'Unidad de personal – convocatorias',
    'Gestión del talento humano y procesos de selección transparentes.',
    {
      body: [
        'Se publican convocatorias para plazas, locaciones de servicios u otras modalidades conforme a la ley laboral y a los perfiles requeridos por la institución.',
        'Los requisitos, plazos y bases integrales se entregan en documento único descargable cuando el proceso esté abierto.',
      ],
    },
  ),
  'docencia-presentacion': page(
    'Apoyo a la docencia e investigación — presentación',
    'Compromiso con la formación de profesionales de la salud y la generación de conocimiento responsable.',
    {
      body: [
        'La clínica articula rotaciones clínicas, tutorías y actividades académicas con instituciones educativas mediante convenios formales, bajo supervisión de la dirección médica.',
        'La investigación se promueve con ética, consentimiento informado y resguardo de datos personales.',
      ],
    },
  ),
  'investigacion-caso-clinico': page(
    'Investigación — concurso caso clínico',
    'Espacio para divulgación de concursos o reconocimientos a la excelencia clínica y educativa.',
    {
      body: [
        'Los concursos de caso clínico fomentan el razonamiento clínico, el trabajo en equipo y la presentación científica. Las bases (elegibilidad, formato, jurados y premios) se publican al inicio de cada convocatoria.',
      ],
    },
  ),
  'docencia-pregrado': page(
    'Docencia pregrado',
    'Programas de práctica preprofesional y rotaciones para estudiantes de carreras de salud.',
    {
      body: [
        'Las rotaciones se coordinan con las escuelas universitarias, con cupos limitados y evaluación por competencias. El estudiante actúa bajo supervisión directa del personal de la institución.',
      ],
    },
  ),
  'docencia-posgrado': page(
    'Docencia posgrado',
    'Residencias médicas, especializaciones o educación continua hospitalaria, según convenios vigentes.',
    {
      body: [
        'La participación en programas de posgrado depende de acreditación institucional y convenios con universidades o colegios profesionales. Consulte a la oficina de docencia para disponibilidad por especialidad.',
      ],
    },
  ),
  'biblioteca-virtual': page(
    'Biblioteca virtual',
    'Recursos digitales internos: protocolos, presentaciones educativas y material de inducción para el personal.',
    {
      body: [
        'El acceso a documentos restringidos se gestiona con credenciales institucionales. El material para pacientes (folletos de preparación a procedimientos, cuidados postoperatorios) puede publicarse en versión pública cuando corresponda.',
      ],
    },
  ),
  convenios: page(
    'Convenios interinstitucionales',
    'Alianzas con otras entidades de salud, educación y seguros para ampliar cobertura y calidad de servicios.',
    {
      body: [
        'Los convenios establecen objetivos, obligaciones, vigencia y mecanismos de seguimiento. La lista de instituciones convenidas y el ámbito del acuerdo (atención, derivación, docencia) pueden consultarse aquí.',
      ],
    },
  ),
  galeria: page(
    'Galería',
    'Registro fotográfico de campañas de salud, inauguraciones y actividades comunitarias.',
    {
      body: [
        'Las imágenes que incluyan personas se publican con consentimiento informado para fines institucionales y respetando la Ley de Protección de Datos Personales.',
      ],
    },
  ),
};

export const prensaPages: Record<string, PortalDoc> = {
  boletines: page(
    'Boletines',
    'Resúmenes periódicos de actividades, campañas y logros institucionales.',
    {
      body: [
        'Los boletines pueden ser mensuales o trimestrales e incluyen cifras de atención, nuevos servicios, reconocimientos al personal y próximas campañas de prevención.',
        'Para suscripción o consulta de ediciones anteriores, utilice el correo de contacto institucional.',
      ],
    },
  ),
  comunicados: page(
    'Comunicados',
    'Notas oficiales dirigidas a medios de comunicación y público en general.',
    {
      body: [
        'Los comunicados informan sobre hechos relevantes: nuevas sedes, cambios de horario, campañas de vacunación conjuntas o respuesta a consultas públicas frecuentes.',
        'Cada comunicado lleva fecha, firma del área emisora y canales de verificación.',
      ],
    },
  ),
};

export const atencionPages: Record<string, PortalDoc> = {
  flujograma: page(
    'Flujograma de atención',
    'Pasos orientativos para el usuario desde que ingresa hasta que culmina su atención.',
    {
      bullets: [
        'Agendar cita por web, teléfono o módulo de citas; recibir confirmación y recordatorios.',
        'Llegar con anticipación; presentar documento de identidad y orden de atención o seguro según corresponda.',
        'Triaje o admisión: registro de signos vitales y derivación a consulta o procedimiento.',
        'Atención médica o de servicio; indicaciones, recetas y próximos controles.',
        'Pago o facturación (si aplica), farmacia, laboratorio o imagen según orden médica.',
        'Alta o salida: documentación, firma de consentimientos pendientes y educación en salud.',
      ],
      body: [
        'Los tiempos pueden variar según complejidad y urgencia. Los casos prioritarios por criterio clínico se atienden según protocolo de emergencias.',
      ],
    },
  ),
  conadis: page(
    'CONADIS y accesibilidad',
    'Compromiso con el acceso a la salud de las personas con discapacidad y con la normativa de accesibilidad.',
    {
      body: [
        'Clínica NovaSalud procura rampas, señalización, prioridad de atención cuando la norma lo exige y comunicación clara para usuarios con distintas capacidades.',
        'Para orientación sobre derechos y programas nacionales, consulte al Consejo Nacional para la Integración de la Persona con Discapacidad (CONADIS).',
      ],
      links: [{ label: 'CONADIS Perú', href: 'https://www.conadis.gob.pe/' }],
    },
  ),
  calidad: page(
    'Oficina de gestión de la calidad',
    'Mejora continua, seguridad del paciente y escucha del usuario.',
    {
      body: [
        'La oficina coordina el sistema de gestión de calidad, indicadores, auditorías internas y gestión de riesgos. Las quejas, reclamos y sugerencias se registran, clasifican y responden en plazos razonables.',
        'Los resultados de encuestas de satisfacción alimentan planes de mejora por proceso.',
      ],
      bullets: [
        'Canales: buzón físico, correo de contacto y atención presencial en horario hábil.',
      ],
    },
  ),
  sis: page(
    'Seguro Integral de Salud (SIS)',
    'Información orientativa para usuarios; los trámites oficiales se realizan en los canales del programa.',
    {
      body: [
        'El SIS es el seguro público que financia el aseguramiento en salud de personas sin seguro privado que cumplen requisitos. La afiliación, renovación y copagos dependen de reglas actualizadas del MINSA.',
        'Si la clínica mantiene convenio con el SIS para determinados servicios, el detalle de cobertura y requisitos se informa en admisión y en esta web.',
      ],
      links: [{ label: 'Portal del SIS — Gobierno del Perú', href: 'https://www.gob.pe/sis' }],
    },
  ),
  tramites: page(
    'Trámites administrativos',
    'Listado orientativo de trámites frecuentes, requisitos y dónde presentarlos.',
    {
      bullets: [
        'Certificados de atención o de procedimientos: solicitud en admisión con DNI y datos de la atención.',
        'Facturación y pago en línea: use Pago en línea (/pagar) con tarjeta (Stripe) o acuda a facturación con comprobante y datos del titular.',
        'Entrega de resultados de laboratorio e imagen: según política de confidencialidad y autorización del paciente.',
        'Devolución de documentación o historial clínico: según normativa sobre historia clínica y autorización expresa.',
        'Convenios empresariales o seguros: mesa de convenios con carta de intención o contacto institucional.',
      ],
      body: [
        'Los plazos de respuesta se informan al momento de la solicitud. Para trámites no listados, escriba a contacto@novasalud.pe indicando asunto y datos de contacto.',
      ],
    },
  ),
};
