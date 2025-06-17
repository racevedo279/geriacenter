# geriatric_management_system/crud_illnesses.py
from sqlalchemy.orm import Session
from geriatric_management_system.models.illness import Illness
from datetime import date # Import date for type hinting

# Create
def create_illness(db: Session, resident_id: int, name: str, diagnosis_date: date = None, notes: str = None) -> Illness:
    db_illness = Illness(
        resident_id=resident_id,
        name=name,
        diagnosis_date=diagnosis_date,
        notes=notes
    )
    db.add(db_illness)
    db.commit()
    db.refresh(db_illness)
    return db_illness

# Read one
def get_illness(db: Session, illness_id: int) -> Illness | None:
    return db.query(Illness).filter(Illness.id == illness_id).first()

# Read all illnesses for a specific resident
def get_illnesses_for_resident(db: Session, resident_id: int, skip: int = 0, limit: int = 100) -> list[Illness]:
    return db.query(Illness).filter(Illness.resident_id == resident_id).offset(skip).limit(limit).all()

# Read all illnesses (across all residents, e.g., for admin or reporting)
def get_all_illnesses(db: Session, skip: int = 0, limit: int = 100) -> list[Illness]:
    return db.query(Illness).offset(skip).limit(limit).all()

# Update
def update_illness(db: Session, illness_id: int, name: str = None, diagnosis_date: date = None, notes: str = None) -> Illness | None:
    db_illness = db.query(Illness).filter(Illness.id == illness_id).first()
    if db_illness:
        if name is not None:
            db_illness.name = name
        if diagnosis_date is not None:
            db_illness.diagnosis_date = diagnosis_date
        if notes is not None: # Allow updating notes, even to an empty string
            db_illness.notes = notes

        db.commit()
        db.refresh(db_illness)
    return db_illness

# Delete
def delete_illness(db: Session, illness_id: int) -> Illness | None:
    db_illness = db.query(Illness).filter(Illness.id == illness_id).first()
    if db_illness:
        db.delete(db_illness)
        db.commit()
    return db_illness
