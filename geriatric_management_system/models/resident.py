from sqlalchemy import Column, Integer, String, Date, Text
from sqlalchemy.orm import relationship
from geriatric_management_system.models import Base # Import Base from the __init__.py

class Resident(Base):
    __tablename__ = 'residents'

    id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    first_name = Column(String, nullable=False)
    last_name = Column(String, nullable=False)
    date_of_birth = Column(Date, nullable=False)
    admission_date = Column(Date, nullable=False)
    emergency_contact_name = Column(String)
    emergency_contact_phone = Column(String)
    room_number = Column(String)

    illnesses = relationship("Illness", back_populates="resident_info")
    medications = relationship("Medication", back_populates="resident_info")

    def __repr__(self):
        return f"<Resident(id={self.id}, name='{self.first_name} {self.last_name}')>"
