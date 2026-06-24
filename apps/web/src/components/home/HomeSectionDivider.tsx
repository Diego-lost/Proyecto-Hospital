import { useId } from 'react';

export type HomeDividerVariant = 'to-image' | 'between' | 'to-muted' | 'to-primary';

type Props = {
  variant?: HomeDividerVariant;
};

/** Onda decorativa entre bloques de la home (estilo Figma). */
export default function HomeSectionDivider({ variant = 'between' }: Props) {
  const uid = useId().replace(/:/g, '');

  if (variant === 'to-image') {
    return (
      <div className="home-section-divider" role="presentation" aria-hidden="true">
        <svg viewBox="0 0 1440 56" preserveAspectRatio="none" className="home-section-divider__svg">
          <defs>
            <linearGradient id={`${uid}-dark`} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#06253a" />
              <stop offset="45%" stopColor="#0a3553" />
              <stop offset="100%" stopColor="#0f4c5c" />
            </linearGradient>
          </defs>
          <path
            fill={`url(#${uid}-dark)`}
            d="M0,56 L1440,56 L1440,26 Q1080,6 720,36 Q360,56 0,16 Z"
          />
          <path
            fill="none"
            stroke="#6ECFC8"
            strokeWidth="2"
            strokeOpacity="0.45"
            d="M0,18 Q360,38 720,22 Q1080,10 1440,24"
          />
        </svg>
      </div>
    );
  }

  if (variant === 'to-muted') {
    return (
      <div className="home-section-divider" role="presentation" aria-hidden="true">
        <svg viewBox="0 0 1440 56" preserveAspectRatio="none" className="home-section-divider__svg">
          <path fill="#edf1f6" d="M0,40 C240,8 480,48 720,28 C960,8 1200,44 1440,24 L1440,56 L0,56 Z" />
        </svg>
      </div>
    );
  }

  if (variant === 'to-primary') {
    return (
      <div className="home-section-divider" role="presentation" aria-hidden="true">
        <svg viewBox="0 0 1440 56" preserveAspectRatio="none" className="home-section-divider__svg">
          <defs>
            <linearGradient id={`${uid}-primary`} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#06253a" />
              <stop offset="50%" stopColor="#0a3553" />
              <stop offset="100%" stopColor="#0a3553" />
            </linearGradient>
          </defs>
          <path
            fill={`url(#${uid}-primary)`}
            d="M0,40 C240,8 480,48 720,28 C960,8 1200,44 1440,24 L1440,56 L0,56 Z"
          />
          <path
            fill="none"
            stroke="#6ECFC8"
            strokeWidth="2"
            strokeOpacity="0.4"
            d="M0,32 C240,6 480,40 720,24 C960,8 1200,38 1440,20"
          />
        </svg>
      </div>
    );
  }

  return (
    <div className="home-section-divider" role="presentation" aria-hidden="true">
      <svg viewBox="0 0 1440 56" preserveAspectRatio="none" className="home-section-divider__svg">
        <defs>
          <linearGradient id={`${uid}-ribbon`} x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#00334d" />
            <stop offset="35%" stopColor="#0a3553" />
            <stop offset="65%" stopColor="#0f766e" />
            <stop offset="100%" stopColor="#00334d" />
          </linearGradient>
        </defs>
        <path
          fill={`url(#${uid}-ribbon)`}
          d="M0,40 C240,8 480,48 720,28 C960,8 1200,44 1440,24 L1440,56 L0,56 Z"
        />
        <path
          fill="none"
          stroke="#6ECFC8"
          strokeWidth="2.5"
          strokeOpacity="0.55"
          d="M0,32 C240,6 480,40 720,24 C960,8 1200,38 1440,20"
        />
      </svg>
    </div>
  );
}

/** Onda blanca al pie del hero (transición hacia sección clara). */
export function HomeHeroWave() {
  return (
    <div className="home-section-divider home-section-divider--hero" role="presentation" aria-hidden="true">
      <svg viewBox="0 0 1440 50" preserveAspectRatio="none" className="home-section-divider__svg home-section-divider__svg--hero">
        <path fill="#ffffff" d="M0,50 L1440,50 L1440,20 Q1080,0 720,30 Q360,50 0,10 Z" />
      </svg>
    </div>
  );
}
