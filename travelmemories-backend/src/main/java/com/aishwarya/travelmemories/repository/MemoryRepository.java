package com.aishwarya.travelmemories.repository;
import com.aishwarya.travelmemories.model.Memory;
import org.springframework.data.jpa.repository.JpaRepository;
public interface MemoryRepository extends JpaRepository<Memory, Long> {}