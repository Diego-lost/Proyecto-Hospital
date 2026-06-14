import type { TriageDolorResult } from '../lib/remoteCatalog';

const ACCION_LABEL: Record<TriageDolorResult['accion_recomendada'], string> = {
  autocuidado: 'Autocuidado y observación',
  consulta_24h: 'Consulta médica en 24 horas',
  urgencias: 'Acudir a urgencias',
};

const RIESGO_LABEL: Record<TriageDolorResult['nivel_riesgo'], string> = {
  bajo: 'Riesgo bajo',
  medio: 'Riesgo medio',
  alto: 'Riesgo alto',
};

type Props = {
  result: TriageDolorResult;
};

export default function TriageReportCard({ result }: Props) {
  const causas = result.posibles_causas ?? [];

  return (
    <article className="ai-triage-report">
      {result.intro ? <p className="ai-triage-report__intro">{result.intro}</p> : null}

      {causas.length > 0 ? (
        <>
          <h3 className="ai-triage-report__heading">Posibles causas del dolor</h3>
          <p className="ai-triage-report__lead">
            Según tu descripción, podrían estar relacionados con condiciones como:
          </p>
          <ul className="ai-triage-report__list">
            {causas.map((c) => (
              <li key={c.titulo} className="ai-triage-report__item">
                <strong>{c.titulo}:</strong> {c.descripcion}
                {c.sintomas_coincidentes.length > 0 ? (
                  <span className="ai-triage-report__symptoms">
                    {' '}
                    Síntomas coincidentes: {c.sintomas_coincidentes.join(', ')}.
                  </span>
                ) : null}
              </li>
            ))}
          </ul>
        </>
      ) : null}

      <div className="ai-triage-report__meta">
        <span className={`ai-triage-report__badge ai-triage-report__badge--${result.nivel_riesgo}`}>
          {RIESGO_LABEL[result.nivel_riesgo]}
        </span>
        <span className="ai-triage-report__action">{ACCION_LABEL[result.accion_recomendada]}</span>
      </div>

      {result.especialidad_sugerida ? (
        <p className="ai-triage-report__specialty">
          Especialidad sugerida: <strong>{result.especialidad_sugerida}</strong>
        </p>
      ) : null}

      {result.senales_alarma.length > 0 ? (
        <p className="ai-triage-report__alert">
          Señales de alarma: {result.senales_alarma.join(', ')}
        </p>
      ) : null}

      {result.recomendaciones_generales.length > 0 ? (
        <>
          <h3 className="ai-triage-report__heading ai-triage-report__heading--sub">
            Cómo aliviarlo / qué puedes hacer
          </h3>
          <ul className="ai-triage-report__tips">
            {result.recomendaciones_generales.map((tip) => (
              <li key={tip}>{tip}</li>
            ))}
          </ul>
        </>
      ) : null}

      <p className="ai-triage-report__disclaimer">{result.disclaimer_peru}</p>
    </article>
  );
}
