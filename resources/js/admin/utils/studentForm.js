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

export const buildStudentFormData = (form, photo, idProof) => {
  const fd = new FormData()
  Object.entries(form).forEach(([key, value]) => {
    if (value !== '' && value != null) fd.append(key, value)
  })
  if (photo) fd.append('photo', photo)
  if (idProof) fd.append('id_proof', idProof)
  return fd
}

const statusValue = (s) => {
  const st = s?.status
  if (st && typeof st === 'object' && st.value) return String(st.value)
  return String(st ?? 'pending')
}

export const studentToForm = (s) => ({
  registration_no: String(s.registration_no ?? ''),
  name: String(s.name ?? s.student_name ?? ''),
  father_name: String(s.father_name ?? ''),
  university_roll_no: String(s.university_roll_no ?? ''),
  college_roll_no: String(s.college_roll_no ?? ''),
  college_name: String(s.college_name ?? s.college?.college_name ?? ''),
  subject: String(s.subject ?? ''),
  semester: String(s.semester ?? ''),
  mobile: String(s.mobile ?? s.mobile_number ?? ''),
  email: String(s.email ?? ''),
  internship_mode: String(s.internship_mode ?? 'online'),
  address: String(s.address ?? ''),
  status: statusValue(s),
  rejection_reason: String(s.rejection_reason ?? ''),
})
