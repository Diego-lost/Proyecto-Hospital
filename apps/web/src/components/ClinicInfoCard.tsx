import { Link } from 'react-router-dom';
import { Calendar, MapPin, Phone } from 'lucide-react';
import type { ClinicInfoBlock } from '../config/clinicInfo';
import { CLINIC } from '../config/clinicInfo';

type Props = {
  info: ClinicInfoBlock;
};

export default function ClinicInfoCard({ info }: Props) {
  return (
    <article className="ai-clinic-card">
      <h3 className="ai-clinic-card__title">{info.title}</h3>
      <ul className="ai-clinic-card__list">
        {info.lines.map((line) => (
          <li key={line}>{line}</li>
        ))}
      </ul>
      {info.links.length > 0 ? (
        <div className="ai-clinic-card__links">
          {info.links.map((link) =>
            link.external || link.href.startsWith('tel:') || link.href.startsWith('mailto:') ? (
              <a key={`${link.label}-${link.href}`} href={link.href} className="ai-clinic-card__link">
                {link.label}
              </a>
            ) : (
              <Link key={`${link.label}-${link.href}`} to={link.href} className="ai-clinic-card__link">
                {link.label}
              </Link>
            ),
          )}
        </div>
      ) : null}
    </article>
  );
}

export function AssistantQuickActions() {
  return (
    <div className="ai-assistant-actions">
      <Link to={CLINIC.citaPath} className="ai-assistant-actions__btn">
        <Calendar size={14} aria-hidden="true" />
        Agendar cita
      </Link>
      <a href={`tel:${CLINIC.phoneTel}`} className="ai-assistant-actions__btn">
        <Phone size={14} aria-hidden="true" />
        Llamar
      </a>
      <Link to={CLINIC.sedesPath} className="ai-assistant-actions__btn">
        <MapPin size={14} aria-hidden="true" />
        Ubicación
      </Link>
    </div>
  );
}
