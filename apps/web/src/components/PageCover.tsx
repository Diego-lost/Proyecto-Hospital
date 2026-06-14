import { publicAsset } from '../lib/publicAsset';

export type PageCoverProps = {
  title: string;
  subtitle?: string;
  pill?: string;
  image?: string;
};

export default function PageCover({ title, subtitle, pill, image = 'portada-banda.svg' }: PageCoverProps) {
  const src = publicAsset(`img/${image.replace(/^img\//, '')}`);

  return (
    <div className="relative overflow-hidden bg-primary" role="banner">
      <img className="absolute inset-0 h-full w-full object-cover opacity-25" src={src} alt="" decoding="async" />
      <div className="absolute inset-0 bg-gradient-to-r from-primary via-primary/90 to-primary/70" aria-hidden="true" />
      <div className="relative mx-auto max-w-7xl px-6 py-14 md:py-20">
        {pill ? <p className="mb-3 inline-block rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-[#6ECFC8]">{pill}</p> : null}
        <h1 className="font-display text-3xl font-bold text-white md:text-4xl">{title}</h1>
        {subtitle ? <p className="mt-3 max-w-2xl text-base text-white/75">{subtitle}</p> : null}
      </div>
    </div>
  );
}
