/** Student portal: /student, /student/dashboard — NOT admin /students */
export function isStudentPortalPath(path: string): boolean {
  return path === '/student' || path === '/student/' || path.startsWith('/student/')
}
