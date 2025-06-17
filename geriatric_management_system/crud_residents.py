# geriatric_management_system/crud_residents.py
from sqlalchemy.orm import Session
from geriatric_management_system.models.resident import Resident
from datetime import date # Import date for type hinting

# Create
def create_resident(db: Session, first_name: str, last_name: str, date_of_birth: date, admission_date: date, emergency_contact_name: str = None, emergency_contact_phone: str = None, room_number: str = None) -> Resident:
    db_resident = Resident(
        first_name=first_name,
        last_name=last_name,
        date_of_birth=date_of_birth,
        admission_date=admission_date,
        emergency_contact_name=emergency_contact_name,
        emergency_contact_phone=emergency_contact_phone,
        room_number=room_number
    )
    db.add(db_resident)
    db.commit()
    db.refresh(db_resident)
    return db_resident

# Read one
def get_resident(db: Session, resident_id: int) -> Resident | None:
    return db.query(Resident).filter(Resident.id == resident_id).first()

# Read many
def get_residents(db: Session, skip: int = 0, limit: int = 100) -> list[Resident]:
    return db.query(Resident).offset(skip).limit(limit).all()

# Update
def update_resident(db: Session, resident_id: int, first_name: str = None, last_name: str = None, date_of_birth: date = None, admission_date: date = None, emergency_contact_name: str = None, emergency_contact_phone: str = None, room_number: str = None) -> Resident | None:
    db_resident = db.query(Resident).filter(Resident.id == resident_id).first()
    if db_resident:
        if first_name is not None:
            db_resident.first_name = first_name
        if last_name is not None:
            db_resident.last_name = last_name
        if date_of_birth is not None:
            db_resident.date_of_birth = date_of_birth
        if admission_date is not None:
            db_resident.admission_date = admission_date
        if emergency_contact_name is not None:
            db_resident.emergency_contact_name = emergency_contact_name
        if emergency_contact_phone is not None:
            db_resident.emergency_contact_phone = emergency_contact_phone
        if room_number is not None:
            db_resident.room_number = room_number

        db.commit()
        db.refresh(db_resident)
    return db_resident

# Delete
def delete_resident(db: Session, resident_id: int) -> Resident | None:
    db_resident = db.query(Resident).filter(Resident.id == resident_id).first()
    if db_resident:
        db.delete(db_resident)
        db.commit()
    return db_resident
