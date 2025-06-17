# geriatric_management_system/crud_medications.py
from sqlalchemy.orm import Session
from geriatric_management_system.models.medication import Medication
from datetime import date # Import date for type hinting

# Create
def create_medication(db: Session, resident_id: int, name: str, dosage: str = None, frequency: str = None, start_date: date = None, end_date: date = None, prescribing_doctor: str = None) -> Medication:
    db_medication = Medication(
        resident_id=resident_id,
        name=name,
        dosage=dosage,
        frequency=frequency,
        start_date=start_date,
        end_date=end_date,
        prescribing_doctor=prescribing_doctor
    )
    db.add(db_medication)
    db.commit()
    db.refresh(db_medication)
    return db_medication

# Read one
def get_medication(db: Session, medication_id: int) -> Medication | None:
    return db.query(Medication).filter(Medication.id == medication_id).first()

# Read all medications for a specific resident
def get_medications_for_resident(db: Session, resident_id: int, skip: int = 0, limit: int = 100) -> list[Medication]:
    return db.query(Medication).filter(Medication.resident_id == resident_id).offset(skip).limit(limit).all()

# Read all medications (across all residents, e.g., for admin purposes)
def get_all_medications(db: Session, skip: int = 0, limit: int = 100) -> list[Medication]:
    return db.query(Medication).offset(skip).limit(limit).all()

# Update
def update_medication(db: Session, medication_id: int, name: str = None, dosage: str = None, frequency: str = None, start_date: date = None, end_date: date = None, prescribing_doctor: str = None) -> Medication | None:
    db_medication = db.query(Medication).filter(Medication.id == medication_id).first()
    if db_medication:
        if name is not None:
            db_medication.name = name
        if dosage is not None:
            db_medication.dosage = dosage
        if frequency is not None:
            db_medication.frequency = frequency
        if start_date is not None:
            db_medication.start_date = start_date
        # Allow clearing end_date by passing None, or setting it
        db_medication.end_date = end_date
        if prescribing_doctor is not None:
            db_medication.prescribing_doctor = prescribing_doctor

        db.commit()
        db.refresh(db_medication)
    return db_medication

# Delete
def delete_medication(db: Session, medication_id: int) -> Medication | None:
    db_medication = db.query(Medication).filter(Medication.id == medication_id).first()
    if db_medication:
        db.delete(db_medication)
        db.commit()
    return db_medication
