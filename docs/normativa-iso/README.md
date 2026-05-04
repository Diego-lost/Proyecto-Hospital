# Normativa ISO — estructura documental del proyecto

Esta jerarquía replica la **lógica de la ISO 9001:2015** (cláusulas 4 a 10) para ubicar documentación del sistema de gestión **del proyecto software + procesos de la clínica** en el repositorio.

| Carpeta | Cláusula ISO 9001 | Contenido típico |
|--------|-------------------|------------------|
| `04-contexto-organizacion/` | 4 | Contexto de la organización: partes interesadas, alcance del SGC, mapa de procesos. |
| `05-liderazgo/` | 5 | Política de calidad, roles y responsabilidades. |
| `06-planificacion/` | 6 | Objetivos de calidad, gestión de riesgos y oportunidades. |
| `07-soporte/` | 7 | Recursos, competencias, infraestructura, información documentada. |
| `08-operacion/` | 8 | Procesos operativos: cómo se entrega el software / la atención (flujos). |
| `09-evaluacion/` | 9 | Seguimiento, medición, auditorías internas, revisión por la dirección. |
| `10-mejora/` | 10 | No conformidades, acciones correctivas, mejora continua. |
| `procedimientos/` | — | Procedimientos documentados (plantillas PDF/Markdown según norma interna). |
| `registros/` | — | Evidencias (plantillas de lista de chequeo; los registros reales pueden estar en otro medio controlado). |
| `instructivos/` | — | Instructivos de trabajo puntuales (menor nivel que procedimiento). |

## Relación con el código

- **`frontend/`** — Sitio público estático.
- **`backend/api/`** — API Laravel (admin, solicitudes, catálogo).
- **`tools/`** — Scripts de desarrollo (p. ej. sincronización frontend → `public/clinica`).

Las **evidencias técnicas** (tests automatizados, CI, revisiones de código) pueden referenciarse desde `07-soporte/` o `09-evaluacion/` mediante enlaces o breves índices.

## Uso

Añade aquí solo documentación **controlada** (versionada). Para borradores, usa ramas de Git o carpeta acordada antes de “publicar” en esta estructura.
