import argparse
from datetime import datetime, date # Make sure datetime is imported for parsing
from sqlalchemy.orm import Session

from geriatric_management_system.database import init_db, get_db
from geriatric_management_system.models.resident import Resident # Needed for type hints if any
from geriatric_management_system.crud_residents import create_resident, get_resident, get_residents, update_resident, delete_resident
from geriatric_management_system.crud_medications import create_medication, get_medications_for_resident, get_medication, update_medication, delete_medication
from geriatric_management_system.crud_illnesses import create_illness, get_illnesses_for_resident, get_illness, update_illness, delete_illness
from geriatric_management_system.reports.individual_reports import get_individual_resident_report_data, print_resident_report
from geriatric_management_system.reports.comprehensive_reports import (
    get_all_residents_summary, print_all_residents_summary,
    get_all_medications_report, print_all_medications_report,
    get_all_illnesses_report, print_all_illnesses_report
)

# Helper to parse date strings
def valid_date(s):
    try:
        return datetime.strptime(s, "%Y-%m-%d").date()
    except ValueError:
        msg = f"Not a valid date: '{s}'. Expected YYYY-MM-DD."
        raise argparse.ArgumentTypeError(msg)

def handle_add_resident(args, db: Session):
    try:
        resident = create_resident(
            db=db,
            first_name=args.first_name,
            last_name=args.last_name,
            date_of_birth=args.dob,
            admission_date=args.admission_date,
            emergency_contact_name=args.emergency_name,
            emergency_contact_phone=args.emergency_phone,
            room_number=args.room
        )
        print(f"Resident {resident.first_name} {resident.last_name} (ID: {resident.id}) added successfully.")
    except Exception as e:
        print(f"Error adding resident: {e}")

def handle_view_resident(args, db: Session):
    report_data = get_individual_resident_report_data(db, args.id)
    if report_data:
        print_resident_report(report_data)
    else:
        print(f"Resident with ID {args.id} not found.")

def handle_add_medication(args, db: Session):
    # Check if resident exists
    if not get_resident(db, args.resident_id):
        print(f"Error: Resident with ID {args.resident_id} not found. Cannot add medication.")
        return
    try:
        med = create_medication(
            db=db,
            resident_id=args.resident_id,
            name=args.name,
            dosage=args.dosage,
            frequency=args.frequency,
            start_date=args.start_date,
            end_date=args.end_date,
            prescribing_doctor=args.doctor
        )
        print(f"Medication {med.name} (ID: {med.id}) added for resident ID {med.resident_id}.")
    except Exception as e:
        print(f"Error adding medication: {e}")

def handle_add_illness(args, db: Session):
    # Check if resident exists
    if not get_resident(db, args.resident_id):
        print(f"Error: Resident with ID {args.resident_id} not found. Cannot add illness.")
        return
    try:
        illness = create_illness(
            db=db,
            resident_id=args.resident_id,
            name=args.name,
            diagnosis_date=args.diagnosis_date,
            notes=args.notes
        )
        print(f"Illness {illness.name} (ID: {illness.id}) added for resident ID {illness.resident_id}.")
    except Exception as e:
        print(f"Error adding illness: {e}")

def handle_list_residents(args, db: Session):
    summary = get_all_residents_summary(db)
    print_all_residents_summary(summary)

def handle_list_medications(args, db: Session):
    report = get_all_medications_report(db)
    print_all_medications_report(report)

def handle_list_illnesses(args, db: Session):
    report = get_all_illnesses_report(db)
    print_all_illnesses_report(report)


def main():
    init_db()  # Ensure DB is initialized
    db: Session = next(get_db())

    parser = argparse.ArgumentParser(description="Geriatric Management System CLI")
    subparsers = parser.add_subparsers(dest="command", help="Available commands", required=True)

    # Add Resident
    parser_add_resident = subparsers.add_parser("add_resident", help="Add a new resident")
    parser_add_resident.add_argument("--first_name", required=True, help="First name")
    parser_add_resident.add_argument("--last_name", required=True, help="Last name")
    parser_add_resident.add_argument("--dob", required=True, type=valid_date, help="Date of birth (YYYY-MM-DD)")
    parser_add_resident.add_argument("--admission_date", required=True, type=valid_date, help="Admission date (YYYY-MM-DD)")
    parser_add_resident.add_argument("--emergency_name", help="Emergency contact name")
    parser_add_resident.add_argument("--emergency_phone", help="Emergency contact phone")
    parser_add_resident.add_argument("--room", help="Room number")
    parser_add_resident.set_defaults(func=handle_add_resident)

    # View Resident
    parser_view_resident = subparsers.add_parser("view_resident", help="View a resident's details and medical information")
    parser_view_resident.add_argument("id", type=int, help="Resident ID")
    parser_view_resident.set_defaults(func=handle_view_resident)

    # Add Medication
    parser_add_med = subparsers.add_parser("add_medication", help="Add medication for a resident")
    parser_add_med.add_argument("--resident_id", type=int, required=True, help="Resident ID for the medication")
    parser_add_med.add_argument("--name", required=True, help="Medication name")
    parser_add_med.add_argument("--dosage", help="Dosage (e.g., 10mg)")
    parser_add_med.add_argument("--frequency", help="Frequency (e.g., Once a day)")
    parser_add_med.add_argument("--start_date", type=valid_date, help="Start date (YYYY-MM-DD)")
    parser_add_med.add_argument("--end_date", type=valid_date, help="End date (YYYY-MM-DD), omit if ongoing")
    parser_add_med.add_argument("--doctor", help="Prescribing doctor")
    parser_add_med.set_defaults(func=handle_add_medication)

    # Add Illness
    parser_add_illness = subparsers.add_parser("add_illness", help="Add an illness for a resident")
    parser_add_illness.add_argument("--resident_id", type=int, required=True, help="Resident ID for the illness")
    parser_add_illness.add_argument("--name", required=True, help="Illness name")
    parser_add_illness.add_argument("--diagnosis_date", type=valid_date, help="Diagnosis date (YYYY-MM-DD)")
    parser_add_illness.add_argument("--notes", help="Additional notes")
    parser_add_illness.set_defaults(func=handle_add_illness)

    # List All Residents
    parser_list_residents = subparsers.add_parser("list_residents", help="List summary of all residents")
    parser_list_residents.set_defaults(func=handle_list_residents)

    # List All Medications
    parser_list_meds = subparsers.add_parser("list_medications", help="List all medications administered")
    parser_list_meds.set_defaults(func=handle_list_medications)

    # List All Illnesses
    parser_list_illnesses = subparsers.add_parser("list_illnesses", help="List all diagnosed illnesses")
    parser_list_illnesses.set_defaults(func=handle_list_illnesses)

    args = parser.parse_args()
    args.func(args, db)

    db.close()

if __name__ == "__main__":
    main()
