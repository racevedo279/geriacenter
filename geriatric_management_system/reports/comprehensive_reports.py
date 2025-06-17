# geriatric_management_system/reports/comprehensive_reports.py
from sqlalchemy.orm import Session
from geriatric_management_system.models.resident import Resident
from geriatric_management_system.models.medication import Medication
from geriatric_management_system.models.illness import Illness
from sqlalchemy import func # For count

def get_all_residents_summary(db: Session) -> list[dict]:
    """Returns a summary list of all residents."""
    residents = db.query(Resident).order_by(Resident.last_name, Resident.first_name).all()
    summary = []
    for res in residents:
        summary.append({
            "id": res.id,
            "full_name": f"{res.first_name} {res.last_name}",
            "date_of_birth": str(res.date_of_birth) if res.date_of_birth else None,
            "admission_date": str(res.admission_date) if res.admission_date else None,
            "room_number": res.room_number
        })
    return summary

def get_all_medications_report(db: Session) -> list[dict]:
    """Returns a list of all medications, including which resident they are for."""
    medications = db.query(Medication, Resident.first_name, Resident.last_name).        join(Resident, Resident.id == Medication.resident_id).        order_by(Resident.last_name, Resident.first_name, Medication.name).        all()

    report = []
    for med, res_first_name, res_last_name in medications:
        report.append({
            "medication_id": med.id,
            "medication_name": med.name,
            "dosage": med.dosage,
            "frequency": med.frequency,
            "prescribed_for_resident_id": med.resident_id,
            "resident_name": f"{res_first_name} {res_last_name}",
            "start_date": str(med.start_date) if med.start_date else None,
            "end_date": str(med.end_date) if med.end_date else "Ongoing",
            "prescribing_doctor": med.prescribing_doctor
        })
    return report

def get_all_illnesses_report(db: Session) -> list[dict]:
    """Returns a list of all diagnosed illnesses, including which resident has them."""
    illnesses = db.query(Illness, Resident.first_name, Resident.last_name).        join(Resident, Resident.id == Illness.resident_id).        order_by(Resident.last_name, Resident.first_name, Illness.name).        all()

    report = []
    for ill, res_first_name, res_last_name in illnesses:
        report.append({
            "illness_id": ill.id,
            "illness_name": ill.name,
            "diagnosed_for_resident_id": ill.resident_id,
            "resident_name": f"{res_first_name} {res_last_name}",
            "diagnosis_date": str(ill.diagnosis_date) if ill.diagnosis_date else None,
            "notes": ill.notes
        })
    return report

# --- Example Printing Functions ---
def print_all_residents_summary(summary: list[dict]):
    print("\n--- All Residents Summary ---")
    if not summary:
        print("No residents found.")
        return
    for res in summary:
        print(f"  ID: {res['id']}, Name: {res['full_name']}, DOB: {res['date_of_birth']}, Room: {res['room_number']}")
    print(f"Total Residents: {len(summary)}")

def print_all_medications_report(report: list[dict]):
    print("\n--- All Medications Report ---")
    if not report:
        print("No medications found.")
        return
    for med in report:
        print(f"  Med: {med['medication_name']} (ID: {med['medication_id']}) for {med['resident_name']} (Res ID: {med['prescribed_for_resident_id']})")
        print(f"    Dosage: {med['dosage']}, Freq: {med['frequency']}, Start: {med['start_date']}, End: {med['end_date']}")
    print(f"Total Medication Records: {len(report)}")

def print_all_illnesses_report(report: list[dict]):
    print("\n--- All Illnesses Report ---")
    if not report:
        print("No illnesses found.")
        return
    for ill in report:
        print(f"  Illness: {ill['illness_name']} (ID: {ill['illness_id']}) for {ill['resident_name']} (Res ID: {ill['diagnosed_for_resident_id']})")
        print(f"    Diagnosis Date: {ill['diagnosis_date']}, Notes: {ill['notes']}")
    print(f"Total Illness Records: {len(report)}")
