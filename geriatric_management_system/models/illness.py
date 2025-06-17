from sqlalchemy import Column, Integer, String, Date, Text, ForeignKey
from sqlalchemy.orm import relationship
from geriatric_management_system.models import Base # Import Base from the __init__.py

class Illness(Base):
    __tablename__ = 'illnesses'

    id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    resident_id = Column(Integer, ForeignKey('residents.id'), nullable=False, index=True)
    name = Column(String, nullable=False)
    diagnosis_date = Column(Date)
    notes = Column(Text)

    resident_info = relationship("Resident", back_populates="illnesses")

    def __repr__(self):
        return f"<Illness(id={self.id}, name='{self.name}', resident_id={self.resident_id})>"
