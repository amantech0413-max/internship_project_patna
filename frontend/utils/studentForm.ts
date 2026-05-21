export const emptyStudentForm = () => ({
  name: '',
  father_name: '',
  university_roll_no: '',
  college_roll_no: '',
  college_name: '',
  subject: '',
  semester: '',
  mobile: '',
  email: '',
  internship_mode: 'online',
  address: '',
  status: 'approved',
  rejection_reason: '',
})

export const buildStudentFormData = (
  form: Record<string, string>,
  photo: File | null,
  idProof: File | null
) => {
  const fd = new FormData()
  Object.entries(form).forEach(([key, value]) => {
    if (value === '' || value == null) return
    fd.append(key, value)
  })
  if (photo) fd.append('photo', photo)
  if (idProof) fd.append('id_proof', idProof)
  return fd
}

export const studentToForm = (s: Record<string, unknown>) => ({
  name: String(s.name ?? s.student_name ?? ''),
  father_name: String(s.father_name ?? ''),
  university_roll_no: String(s.university_roll_no ?? ''),
  college_roll_no: String(s.college_roll_no ?? ''),
  college_name: String(s.college_name ?? ''),
  subject: String(s.subject ?? ''),
  semester: String(s.semester ?? ''),
  mobile: String(s.mobile ?? s.mobile_number ?? ''),
  email: String(s.email ?? ''),
  internship_mode: String(s.internship_mode ?? 'online'),
  address: String(s.address ?? ''),
  status: String(s.status ?? 'pending'),
  rejection_reason: String(s.rejection_reason ?? ''),
})
