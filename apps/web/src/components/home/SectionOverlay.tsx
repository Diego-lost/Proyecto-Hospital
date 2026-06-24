import type { ReactNode } from 'react';

type SectionOverlayProps = {
  id?: string;
  image: string;
  imageAlt?: string;
  children: ReactNode;
  minHeight?: 'md' | 'lg';
  className?: string;
};

export default function SectionOverlay({
  id,
  image,
  imageAlt = '',
  children,
  minHeight = 'lg',
  className = '',
}: SectionOverlayProps) {
  const minH = minHeight === 'lg' ? 'min-h-[min(480px,88vh)]' : 'min-h-[min(400px,80vh)]';

  return (
    <section id={id} className={`relative overflow-hidden ${minH} ${className}`}>
      <div
        className="absolute inset-0 scale-105 bg-cover bg-center bg-no-repeat transition-transform duration-[8s] ease-out"
        style={{ backgroundImage: `url(${image})` }}
        role="img"
        aria-label={imageAlt}
      />
      <div
        className="absolute inset-0"
        style={{
          background: `linear-gradient(
              105deg,
              rgba(6, 37, 58, 0.9) 0%,
              rgba(0, 51, 77, 0.78) 42%,
              rgba(15, 118, 110, 0.45) 100%
            ),
            linear-gradient(0deg, rgba(4, 12, 28, 0.55) 0%, transparent 45%)`,
        }}
        aria-hidden
      />
      <div className="relative z-10 flex flex-col justify-center py-16 md:py-24">{children}</div>
    </section>
  );
}
