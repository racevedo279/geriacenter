# geriatric_management_system/reports/individual_reports.py
from sqlalchemy.orm import Session, joinedload
from geriatric_management_system.models.resident import Resident
# Ensure Medication and Illness models are loaded for relationship hydration
from geriatric_management_system.models.medication import Medication
from geriatric_management_system.models.illness import Illness

def get_individual_resident_report_data(db: Session, resident_id: int) -> dict | None:
    """
    Fetches all data for a single resident, including their medications and illnesses.
    Uses joinedload to optimize fetching related objects.
    """
    resident = db.query(Resident).        options(
            joinedload(Resident.medications),
            joinedload(Resident.illnesses)
        ).        filter(Resident.id == resident_id).        first()

    if not resident:
        return None

    report_data = {
        "personal_info": {
            "id": resident.id,
            "first_name": resident.first_name,
            "last_name": resident.last_name,
            "date_of_birth": str(resident.date_of_birth) if resident.date_of_birth else None,
            "admission_date": str(resident.admission_date) if resident.admission_date else None,
            "emergency_contact_name": resident.emergency_contact_name,
            "emergency_contact_phone": resident.emergency_contact_phone,
            "room_number": resident.room_number,
        },
        "medications": [
            {
                "id": med.id,
                "name": med.name,
                "dosage": med.dosage,
                "frequency": med.frequency,
                "start_date": str(med.start_date) if med.start_date else None,
                "end_date": str(med.end_date) if med.end_date else None,
                "prescribing_doctor": med.prescribing_doctor,
            } for med in resident.medications
        ],
        "illnesses": [
            {
                "id": ill.id,
                "name": ill.name,
                "diagnosis_date": str(ill.diagnosis_date) if ill.diagnosis_date else None,
                "notes": ill.notes,
            } for ill in resident.illnesses
        ]
    }
    return report_data

def print_resident_report(report_data: dict):
    """Prints the resident report data to the console in a readable format."""
    if not report_data:
        print("No data to print.")
        return

    print("\n--- Resident Report ---")
    personal_info = report_data["personal_info"]
    print("\n[Personal Information]")
    for key, value in personal_info.items():
        print(f"  {key.replace('_', ' ').title()}: {value if value is not None else 'N/A'}")

    print("\n[Medications]")
    if report_data["medications"]:
        for med in report_data["medications"]:
            print(f"  - Name: {med['name']} (ID: {med['id']})")
            print(f"    Dosage: {med['dosage']}, Frequency: {med['frequency']}")
            print(f"    Start Date: {med['start_date']}, End Date: {med['end_date'] if med['end_date'] else 'Ongoing'}")
            print(f"    Prescribing Doctor: {med['prescribing_doctor'] if med['prescribing_doctor'] else 'N/A'}")
    else:
        print("  No medications listed.")

    print("\n[Illnesses]")
    if report_data["illnesses"]:
        for ill in report_data["illnesses"]:
            print(f"  - Name: {ill['name']} (ID: {ill['id']})")
            print(f"    Diagnosis Date: {ill['diagnosis_date']}")
            print(f"    Notes: {ill['notes']}")
    else:
        print("  No illnesses listed.")
    print("\n--- End of Report ---")
