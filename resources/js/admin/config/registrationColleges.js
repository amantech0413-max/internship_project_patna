export const PROGRAM_TITLE = 'M/s Bhagya Laxmi Internship Jun 2026'

/** slug → full college label (must match config/internship_registration.php) */
export const REGISTRATION_COLLEGES = [
  {
    slug: 'bhagya-laxmi-internship-jun-2026-womens-college-samastipur',
    name: "Bhagya Laxmi Internship Jun 2026 Women's college Samastipur",
    shortName: "Women's college Samastipur",
  },
  {
    slug: 'bhagya-laxmi-internship-jun-2026-m-k-s-college-chandauna',
    name: 'Bhagya Laxmi Internship Jun 2026 M k s college chandauna',
    shortName: 'M k s college chandauna',
  },
  {
    slug: 'bhagya-laxmi-internship-jun-2026-l-n-j-college-jhanjharpur',
    name: 'Bhagya Laxmi Internship Jun 2026 L n j college Jhanjharpur',
    shortName: 'L n j college Jhanjharpur',
  },
  {
    slug: 'bhagya-laxmi-internship-jun-2026-u-r-college-rosera',
    name: 'Bhagya Laxmi Internship Jun 2026 U r college rosera',
    shortName: 'U r college rosera',
  },
]

export function collegeBySlug(slug) {
  return REGISTRATION_COLLEGES.find((c) => c.slug === slug) || null
}
