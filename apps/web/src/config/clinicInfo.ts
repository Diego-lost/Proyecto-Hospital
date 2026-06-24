export const CLINIC = {
  name: 'Clínica NovaSalud',
  phone: '(01) 123-4567',
  phoneTel: '+51011234567',
  email: 'contacto@novasalud.pe',
  address: 'Av. Giráldez, Huancayo, Junín',
  schedule: 'Lunes a sábado, 8:00 – 20:00',
  emergency: 'Emergencia 24/7',
  citaPath: '/cita',
  sedesPath: '/sedes',
  especialidadesPath: '/especialidades',
  contactoPath: '/contacto',
  pagoPath: '/pagar',
} as const;

export type ClinicTopic =
  | 'cita'
  | 'telefono'
  | 'ubicacion'
  | 'horario'
  | 'especialidades'
  | 'pago'
  | 'contacto';

const TOPIC_PATTERNS: Record<ClinicTopic, RegExp> = {
  cita: /\b(cita|citas|agendar|reservar|turno|appointment)\b/i,
  telefono: /\b(telefono|tel[eé]fono|llamar|numero|n[uú]mero|whatsapp|contactar)\b/i,
  ubicacion: /\b(donde|dónde|ubicaci[oó]n|direcci[oó]n|sedes?|llegar|mapa)\b/i,
  horario: /\b(horario|hora|abierto|atenci[oó]n|emergencia)\b/i,
  especialidades: /\b(especialidad|medico|m[eé]dico|doctor|odontolog|cardiolog)\b/i,
  pago: /\b(pago|pagar|yape|tarjeta|factura|boleta)\b/i,
  contacto: /\b(contacto|correo|email|mail|escribir)\b/i,
};

const MEDICAL_PATTERN =
  /\b(dolor|duele|molest|ardor|picor|fiebre|n[aá]usea|mareo|malestar|s[ií]ntoma|enferm|grip|cabeza|est[oó]mago|diente|espalda|pecho|garganta|tos|v[oó]mito|diarrea|inflam)\b/i;

export function isMedicalQuery(message: string): boolean {
  return MEDICAL_PATTERN.test(message.trim());
}

export function detectClinicTopics(message: string): ClinicTopic[] {
  const text = message.trim();
  if (text === '') {
    return [];
  }
  const topics: ClinicTopic[] = [];
  for (const [topic, pattern] of Object.entries(TOPIC_PATTERNS) as [ClinicTopic, RegExp][]) {
    if (pattern.test(text)) {
      topics.push(topic);
    }
  }
  return topics;
}

export function shouldAnswerWithClinicInfo(message: string): boolean {
  const topics = detectClinicTopics(message);
  if (topics.length === 0) {
    return false;
  }
  return !isMedicalQuery(message);
}

export type ClinicInfoBlock = {
  title: string;
  lines: string[];
  links: { label: string; href: string; external?: boolean }[];
};

export function buildClinicInfoResponse(message: string): ClinicInfoBlock {
  const topics = detectClinicTopics(message);
  const all = topics.length === 0 ? (['contacto'] as ClinicTopic[]) : topics;

  const lines: string[] = [];
  const links: ClinicInfoBlock['links'] = [];

  if (all.includes('cita')) {
    lines.push('Puedes solicitar una cita en línea; te contactamos para confirmar disponibilidad en un plazo de 48 horas hábiles.');
    links.push({ label: 'Agendar cita', href: CLINIC.citaPath });
  }
  if (all.includes('telefono') || all.includes('contacto')) {
    lines.push(`Teléfono: ${CLINIC.phone}`);
    lines.push(`Correo: ${CLINIC.email}`);
    links.push({ label: `Llamar ${CLINIC.phone}`, href: `tel:${CLINIC.phoneTel}`, external: true });
    links.push({ label: 'Ver contacto', href: CLINIC.contactoPath });
  }
  if (all.includes('ubicacion')) {
    lines.push(`Dirección: ${CLINIC.address}`);
    links.push({ label: 'Ver sedes', href: CLINIC.sedesPath });
  }
  if (all.includes('horario')) {
    lines.push(`Horario de atención: ${CLINIC.schedule}`);
    lines.push(CLINIC.emergency);
  }
  if (all.includes('especialidades')) {
    lines.push('Consulta el listado de especialidades y médicos disponibles en nuestro portal.');
    links.push({ label: 'Ver especialidades', href: CLINIC.especialidadesPath });
  }
  if (all.includes('pago')) {
    lines.push('Puedes realizar pagos en línea de forma segura.');
    links.push({ label: 'Pago en línea', href: CLINIC.pagoPath });
  }

  const uniqueLinks = links.filter(
    (link, idx) => links.findIndex((l) => l.href === link.href && l.label === link.label) === idx,
  );

  return {
    title: 'Información de la clínica',
    lines: lines.length > 0 ? lines : [`Estamos en ${CLINIC.address}. Teléfono ${CLINIC.phone}.`],
    links: uniqueLinks,
  };
}
