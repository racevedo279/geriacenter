from sqlalchemy import Column, Integer, String, Date, ForeignKey
from sqlalchemy.orm import relationship
from geriatric_management_system.models import Base # Import Base from the __init__.py

class Medication(Base):
    __tablename__ = 'medications'

    id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    resident_id = Column(Integer, ForeignKey('residents.id'), nullable=False, index=True)
    name = Column(String, nullable=False)
    dosage = Column(String)
    frequency = Column(String)
    start_date = Column(Date)
    end_date = Column(Date, nullable=True) # Nullable for ongoing medications
    prescribing_doctor = Column(String, nullable=True)

    resident_info = relationship("Resident", back_populates="medications")

    def __repr__(self):
        return f"<Medication(id={self.id}, name='{self.name}', resident_id={self.resident_id})>"
