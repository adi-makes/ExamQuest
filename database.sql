-- =============================================================
-- ExamQuest Database - Full Setup (CUSAT B.Tech, 2023 Scheme)
-- Drop-and-recreate for idempotent import in phpMyAdmin
-- =============================================================

CREATE DATABASE IF NOT EXISTS examquest_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE examquest_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS subject;
DROP TABLE IF EXISTS branch;
DROP TABLE IF EXISTS syllabus;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------
-- Table: syllabus
-- -------------------------------------------------------
CREATE TABLE syllabus (
    syllabus_id     INT AUTO_INCREMENT PRIMARY KEY,
    regulation_year VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: branch
-- -------------------------------------------------------
CREATE TABLE branch (
    branch_id   INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: subject
-- -------------------------------------------------------
CREATE TABLE subject (
    subject_id   INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(200) NOT NULL,
    semester     VARCHAR(10)  NOT NULL,
    subject_type VARCHAR(50)  NOT NULL DEFAULT 'Core Subject',
    syllabus_id  INT NOT NULL,
    branch_id    INT NOT NULL,
    UNIQUE KEY uq_subject (subject_name(150), semester, syllabus_id, branch_id),
    FOREIGN KEY (syllabus_id) REFERENCES syllabus(syllabus_id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id)   REFERENCES branch(branch_id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table: questions
-- -------------------------------------------------------
CREATE TABLE questions (
    question_id   INT AUTO_INCREMENT PRIMARY KEY,
    subject_id    INT         NOT NULL,
    question_text MEDIUMTEXT  NOT NULL,
    marks         INT         NOT NULL DEFAULT 0,
    frequency     INT         NOT NULL DEFAULT 1,
    UNIQUE KEY uq_question (subject_id, question_text(500)),
    FOREIGN KEY (subject_id) REFERENCES subject(subject_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- SEED DATA
-- =============================================================

-- Syllabi
INSERT INTO syllabus (regulation_year) VALUES
    ('CUSAT B.Tech 2023 Scheme'),
    ('CUSAT B.Tech 2019 Scheme');

-- Branches
INSERT INTO branch (branch_name) VALUES
    ('Civil Engineering'),
    ('Computer Science and Engineering'),
    ('Electrical Engineering'),
    ('Electronics and Communication Engineering'),
    ('Information Technology'),
    ('Mechanical Engineering'),
    ('Safety and Fire Engineering');

-- =============================================================
-- Subjects (14 unique records, duplicates and errors corrected)
-- ID  Subject Name                                  Sem
--  1  Automata Languages and Computation             4
--  2  Computer Architecture and Organisation         3
--  3  Data and Computer Communication                4
--  4  Data Structures and Algorithms                 3
--  5  Database Management Systems                    4
--  6  Differential Equations and Complex Variables   3
--  7  Discrete Computational Structures              3
--  8  Microprocessors                                4
--  9  Numerical and Statistical Methods              4
-- 10  Numerical and Statistical Techniques           4
-- 11  Object Oriented Software Engineering           4
-- 12  Operating Systems                              4
-- 13  Principles of Programming Languages            3
-- 14  Python for Machine Learning                    4
-- =============================================================
INSERT INTO subject (subject_name, semester, subject_type, syllabus_id, branch_id) VALUES
    ('Automata Languages and Computation',            '4', 'Core Subject', 1, 2),
    ('Computer Architecture and Organisation',        '3', 'Core Subject', 1, 2),
    ('Data and Computer Communication',               '4', 'Core Subject', 1, 2),
    ('Data Structures and Algorithms',                '3', 'Core Subject', 1, 2),
    ('Database Management Systems',                   '4', 'Core Subject', 1, 2),
    ('Differential Equations and Complex Variables',  '3', 'Core Subject', 1, 2),
    ('Discrete Computational Structures',             '3', 'Core Subject', 1, 2),
    ('Microprocessors',                               '4', 'Core Subject', 1, 2),
    ('Numerical and Statistical Methods',             '4', 'Core Subject', 1, 2),
    ('Numerical and Statistical Techniques',          '4', 'Core Subject', 1, 2),
    ('Object Oriented Software Engineering',          '4', 'Core Subject', 1, 2),
    ('Operating Systems',                             '4', 'Core Subject', 1, 2),
    ('Principles of Programming Languages',           '3', 'Core Subject', 1, 2),
    ('Python for Machine Learning',                   '4', 'Core Subject', 1, 2);

-- =============================================================
-- QUESTIONS
-- =============================================================

-- Subject 1: Automata Languages and Computation (Semester 4)
-- Merged from original subjects 1 (ALC) and 2 (ALC with typo)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (1, 'Define a Deterministic Finite Automata. How does it differ from a Non Deterministic Finite Automata? Give example for each.', 4, 1),
    (1, 'Eliminate ε from the following finite automata using ε-closure.', 4, 1),
    (1, 'Write regular expressions for: set of binary strings that have odd number of 0''s followed by odd number of 1''s; strings from {a,b,c} whose length is ≥ 5, starting with ''aa'' and ending with ''cc''; L = {a^n b^m | n ≥ 1, m ≥ 1, n+m ≥ 3}; all strings from Σ = {a,b} which contain at least one occurrence of ''a'' and ''b''; L = {x^n | n is divisible by 3 or 5}.', 4, 1),
    (1, 'Construct NFA for the following expressions starting from the basic expressions for 0 and 1: 1*10* + 011*; (0+1)* 111 (0+1)*.', 4, 1),
    (1, 'Define Moore and Mealy machines. Design a Moore machine where Σ = {a,b}. The machine outputs ''y'' whenever there are three consecutive a''s or three consecutive b''s. In all other cases the output is ''n''.', 4, 1),
    (1, 'Show that the language L = {a^x | where x = i^3 and i > 0} is not regular using pumping lemma. What is a regular grammar? Represent the following grammar as a finite automata: S → aA | bB | c; A → bC | a; B → aB | b.', 10, 1),
    (1, 'Design a DFA for the regular expression 1*(10)*1*.', 10, 1),
    (1, 'Convert the given finite automata to a minimized DFA.', 10, 1),
    (1, 'Using Arden''s theorem find the regular expression for the given finite automata.', 10, 1),
    (1, 'Write a CFG for the language: L(G) = {a^n b^m c^m d^2n | n>0, m>0}; L(G) = {(a^n b^n) | n>0}.', 4, 1),
    (1, 'Show how left recursion is eliminated in the following rules: A → Abc | bB | a; B → Baa | Bbb | c.', 4, 1),
    (1, 'Convert the following grammar to CNF: S → Abc | caB; A → bAB | aB | aAlb; B → AaSB | bSA | b | ε.', 4, 1),
    (1, 'Convert the following grammar to Greibach normal form: S → Abb | ab | c; A → baA | B; B → bAb | cA | a.', 4, 1),
    (1, 'Remove useless symbols from the following grammar: S → 0B | 1X; A → 1SX | BA1 | 0; B → 0SB | 1BX; X → SB0 | 01.', 4, 1),
    (1, 'Consider the following grammar and the input string ''aabbaa'': S → aAS | a; A → SbA | SS | ba. Give the left most derivation of the string; Give the right most derivation of the string; Draw the parse tree of above derivations. Define a Turing machine. Design a Turing machine to find the one''s complement of a binary string.', 10, 1),
    (1, 'Design a PDA to accept L(M) = {a^n b^n c^m d^m | m, n > 0}.', 10, 1),
    (1, 'Design a Turing machine to accept L(M) = {a^n b^n c^n | n > 0}.', 10, 1),
    (1, 'How do you represent the Instantaneous Description (ID) of a Turing machine (TM)? Write the ID of the Turing machine based on given information. Show how the ID changes as transition rules are applied.', 10, 1),
    (1, 'Draw a finite automata accepting the language of all strings over {a,b} in which both number of a''s and b''s are even.', 2, 1),
    (1, 'Give the automation for the regular grammar G = (V,T,P,S) where V = {S,A,B}, T = {a,b} and P is defined as: S → bS | aA | ε; A → aA | bB | b; B → bS.', 2, 1),
    (1, 'Consider the following grammar: S → AB | aaB; A → a | Aa; B → b. Check whether the given grammar is ambiguous. Justify your answer.', 2, 1),
    (1, 'Explain any two techniques for Turing machine construction.', 2, 1),
    (1, 'Write short note on Linear Bounded Automata.', 2, 1),
    (1, 'Convert the following NFA to DFA.', 7, 1),
    (1, 'Construct a Moore machine which calculates residue mod 4 for each binary string treated as binary integer.', 3, 1),
    (1, 'Minimise the given DFA. Convert the given Moore machine into equivalent Mealy machine.', 10, 1),
    (1, 'Using pumping lemma prove that the language L = {a^n b^n | n > 0} is not regular. Construct the finite automata equivalent to the regular expression ((0+1)(0+1))* + ((0+1)(0+1)(0+1))*.', 10, 1),
    (1, 'Find the regular expression for the given transition system. Find the regular expression representing the following sets over {a,b}*: The language of all strings in which every a is immediately followed by bb; The language of all strings containing the substring bba; The language of all strings that do not end with bb.', 10, 1),
    (1, 'Convert to CNF: S → TU | V; T → aTb | ε; U → cU | ε; V → aVc | W; W → bW | ε. Design a push down automata for the language L = {a^n b^(n+1) : n > 0}. Also explain with a suitable sample string.', 10, 1),
    (1, 'Convert the following grammar into GNF: S → XA | BB; B → b | SB; X → b; A → a. Remove the unit productions from the following grammar: S → A | bb; A → B | b; B → S | a.', 10, 1),
    (1, 'Design a Turing machine which accepts the language L = {a^n b^(2n)}. Also explain its workings with a suitable string in the input tape. Give a brief discussion on Universal Turing machine.', 10, 1),
    (1, 'Construct a Turing machine that computes f(n,m) = n*m, where n and m are unary numbers. Write short notes on Chomsky hierarchy.', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 2: Computer Architecture and Organisation (Semester 3)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (2, 'Discuss about the history of computers by explaining the developments in each generation.', 5, 1),
    (2, 'Explain how DDR SDRAM achieves high data transfer rates during block transfers.', 5, 1),
    (2, 'Explain how a CMOS SRAM cell maintains data stability and enables fast access times compared to other memory types, including a supporting figure.', 5, 1),
    (2, 'Explain the different states involved in an instruction cycle state diagram.', 5, 1),
    (2, 'What types of instructions were supported by the IAS computer? Give examples under each type.', 5, 1),
    (2, 'Describe the memory formats with figures and explain the different registers used in the IAS computer architecture.', 10, 1),
    (2, 'Describe interconnection structures in computing. Explain bus interconnection structure and point to point interconnection. Also give a comparison.', 10, 1),
    (2, 'Draw and explain the internal organization of a 16M DRAM chip organised as a 2048 x 2048 x 4 cell array.', 10, 1),
    (2, 'Describe the major components of an IAS computer and how do they interact with each other? Explain with the help of a diagram.', 10, 1),
    (2, 'Write a short note on the Translation Lookaside Buffer (TLB) and its role in virtual memory.', 5, 1),
    (2, 'Describe the working of Direct Memory Access (DMA) and its advantages.', 5, 1),
    (2, 'Write a short note on Floating Point Representation (IEEE 754 standard).', 5, 1),
    (2, 'Compare and contrast Partitioning and Segmentation in memory management.', 5, 1),
    (2, 'Compare and contrast RISC and CISC instruction execution characteristics.', 5, 1),
    (2, 'Explain the concept of Instruction Pipelining stages (Fetch, Decode, Execute, etc.). How does pipelining improve CPU performance? Use a diagram.', 10, 1),
    (2, 'Perform Booth''s multiplication for the following pairs of numbers: 1010 (-10) and 1100 (12); 0110 (6) and 1011 (-5).', 10, 1),
    (2, 'A system uses paging with 4KB page size and 32-bit addresses. Calculate the number of pages and page table entries.', 10, 1),
    (2, 'Explain the working of Interrupt-Driven I/O and its advantages. Provide a diagram illustrating the interrupt handling process.', 10, 1),
    (2, 'What are the main components of a computer system and how are they interconnected?', 2, 1),
    (2, 'Describe the main features of embedded systems and give an example of where they are used.', 2, 1),
    (2, 'Describe the primary difference between flash memory and traditional magnetic disk storage.', 2, 1),
    (2, 'How does PCI bus interconnection enable communication between multiple devices and the processor?', 2, 1),
    (2, 'Compare any three characteristics of RISC and CISC architectures.', 2, 1),
    (2, 'Explain the ARM architecture and discuss its significance in the field of embedded systems.', 10, 1),
    (2, 'What are the main characteristics of the Intel x86 architecture and why has it become significant in computing? Use a block diagram to support your answer.', 10, 1),
    (2, 'Explain the significance of memory hierarchy in a computer system and discuss its main characteristics. What is the purpose of cache memory in a computer system and how does it improve system performance? A computer system has a cache with a hit rate of 0.85 (85%). Which is the best Cache mapping technique to make the hit ratio close to 100%? Explain it briefly.', 10, 1),
    (2, 'Describe Direct Memory Access (DMA) and its role in I/O operations. How does DMA improve system performance compared to Programmed I/O?', 10, 1),
    (2, 'Explain Booth''s Algorithm for binary multiplication. How does it optimize the multiplication process, especially when dealing with signed numbers? Discuss its advantages over traditional multiplication methods.', 10, 1),
    (2, 'Explain the IEEE 754 standard for binary floating-point representation, including the roles of the sign bit, exponent and mantissa. Convert -12.75 into IEEE 754 single-precision format, showing each step.', 10, 1),
    (2, 'Discuss the concept of addressing modes in computer systems. Provide examples of common addressing modes from x86 processor.', 10, 1),
    (2, 'Explain the concept of micro-operations and microinstruction sequencing in a processor. How does the control unit manage and sequence these operations to execute an instruction?', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 3: Data and Computer Communication (Semester 4)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (3, 'Why does the TCP/IP protocol stack not have separate session and presentation layers despite OSI mandating them?', 2, 1),
    (3, 'An n-layer protocol hierarchy adds h-byte headers at each layer for messages of length M bytes. If the total overhead must not exceed 20% of the total transmitted data, derive the inequality relating n, h, and M. For M = 1000 bytes and h = 20 bytes, find the maximum permissible number of protocol layers.', 2, 1),
    (3, 'A composite analog signal is formed by combining three sinusoidal components: 2sin(2π1000t), 5sin(2π3000t + π/4), and 3sin(2π5000t - π/3). Determine the bandwidth of this composite signal. According to Nyquist''s theorem, what minimum sampling rate must be used to perfectly reconstruct the signal without aliasing? If the signal is quantized using 8-bit PCM, what is the resulting bit rate?', 2, 1),
    (3, 'A signal of power 10 mW is transmitted over a medium with an attenuation of 0.5 dB/km. The signal also experiences an additional 3 dB loss at a connector. If the minimum detectable signal power at the receiver is -30 dBm, find (i) the maximum permissible transmission distance and (ii) the received signal power in mW at that distance.', 2, 1),
    (3, 'Draw the configuration showing EIA/TIA 568A/B cross-over and straight cables. Mention the uses of these cables.', 2, 1),
    (3, 'A WAN spanning three cities uses routers running OSPF at the network layer, with TCP at the transport layer and TLS at the session/presentation layer. A new compression algorithm is introduced at layer n (presentation layer). (i) Explain, using the principle of layer independence, what will be the effect on layers n-1 and n+1 due to this change. Why? (ii) Suppose the new algorithm changes the PDU size significantly, analyse any cross-layer effects that could indirectly impact performance at layers n-1 and n+1. Justify your reasoning with examples.', 2, 1),
    (3, 'Draw the ISO/OSI reference model with all layers clearly labelled. For each layer, state its primary function, the PDU name, and the corresponding TCP/IP protocol(s) used. Additionally, trace the complete journey of an HTTP request from a browser to a web server and back, describing the encapsulation/decapsulation process at each layer on both source and destination hosts.', 3, 1),
    (3, 'A high-definition television (HDTV) system transmits frames of resolution 1920 x 1080 pixels at 60 frames per second, where each pixel is encoded with 24-bit colour. (i) Calculate the uncompressed source data rate in Mbps. (ii) The signal is transmitted over a channel with 6 MHz bandwidth and a measured SNR of 40 dB. Compute the Shannon capacity and determine the compression ratio needed to fit the HDTV signal within the channel capacity. (iii) If the channel is noiseless with 6 MHz bandwidth and 256 signal levels, recalculate the maximum data rate and comment on which model gives a higher capacity.', 2, 1),
    (3, 'Two periodic signals are given: f1(t) = 3sin(2π400t) and f2(t) = 5cos(2π600t + π/6). (i) Find the period and frequency of f(t) = f1(t) + f2(t) and prove it is periodic by finding the LCM of their periods. (ii) Determine the bandwidth of f(t). (iii) Suppose a third component f3(t) = 2sin(2π√2·400t) is added. Analyse whether the resulting composite signal is still periodic, providing a mathematical proof, and explain the engineering implications of a non-periodic signal in a digital communication system.', 2, 1),
    (3, 'A digital data source generates data at 28,800 bps and must be transmitted over an analog telephone channel. Calculate the minimum bandwidth required and the baud rate for 2-ASK, 4-ASK, 8-PSK, 16-QAM and 64-QAM. Tabulate the results and identify which scheme achieves the highest spectral efficiency. Also explain why 16-QAM is preferred over 8-PSK for the same bandwidth despite a higher SNR requirement.', 2, 1),
    (3, 'An audio signal has frequency components in the range 20 Hz to 15,000 Hz. It is to be digitized using PCM. (i) State the Nyquist sampling theorem and determine the minimum sampling frequency. (ii) For a desired SNR of 50 dB, calculate the required number of quantization bits. (iii) Compute the resulting PCM bit rate. (iv) Compare PCM with Delta Modulation (DM) for the same signal and why PCM is preferred for signals representing digital data describing higher bit rates.', 2, 1),
    (3, 'For BPSK, Eb/N0 = 7.5 dB is required for a BER of 10^-3. If the effective noise temperature is 27°C and the data rate is 36 kbps, what received signal level is required?', 2, 1),
    (3, 'A 50 km fiber optic link has an attenuation of 0.3 dB/km. The transmitter outputs +20 dBm and the receiver requires a minimum received power of -28 dBm to operate correctly. Splices are present every 5 km with a loss of 0.1 dB each. Connectors at both ends introduce 0.5 dB loss each. (i) Compute the total link loss. (ii) Determine whether the link is feasible without repeaters. (iii) If not feasible, calculate the maximum distance without repeaters and the number and spacing of repeaters needed to cover the full 50 km.', 3, 1),
    (3, 'A sine wave is to be used for two different signaling schemes: (a) PSK, (b) QPSK. The duration of a signal element is 10^-5 s. If the received signal is of the form f(t) = 0.005sin(2π10^6t + θ) and if the measured noise power at the receiver is 2.5 x 10^-8 watts, determine the Eb/N0 (in dB) for each case.', 2, 1),
    (3, 'For the bit stream 01101111111110010, draw the pulse waveform for the following line encoding schemes: (i) NRZ-L, (ii) Manchester, (iii) AMI, (iv) B8ZS and (v) HDB3. State the voltage levels used, method of bit representation, DC component, self-synchronisation capability, and bandwidth requirements relative to the data rate. Which scheme is most suitable for long-distance transmission and justify your answer.', 2, 1),
    (3, 'Draw a table showing the comparison of downstream data rate, upstream data rate, baud rate and modulation scheme for the following modems: (i) V-22bis (ii) V-32 (iii) V-32bis (iv) V-90 (v) V-92. Provide any 5 AT commands that can be used with Hayes-compatible modems.', 2, 1),
    (3, 'What is DMT? Explain how DMT can be effectively used in ADSL with a telephone line.', 2, 2),
    (3, 'Prove that if (x+1) is a factor of G(x), the generator polynomial, CRC can detect all burst errors having odd number of bits in error. Provide any two standard CRC polynomials.', 2, 2),
    (3, 'Three signals with bandwidths of 200 kHz, 500 kHz, and 800 kHz are combined using FDM. A guard band of 25 kHz is required between each channel and at the two edges of the composite band. Determine: (i) the total bandwidth of the composite FDM signal, (ii) the carrier frequency of each sub-channel if the first carrier starts at 1 MHz, (iii) the spectral efficiency of the system, defined as the ratio of usable bandwidth to total allocated bandwidth.', 2, 1),
    (3, 'A frequency-hopping spread spectrum system uses 256 different frequencies with a bandwidth of 25 kHz each. If the hopping rate is 1600 hops per second, what is the processing gain and the required minimum transmission bandwidth?', 2, 2),
    (3, 'Ten sources, six with a bit rate of 200 kbps and four with a bit rate of 400 kbps, are to be combined using multilevel TDM with no synchronising bits. Draw the multiplexing scheme. Answer the following with the final stage of the multiplexing: (i) What is the size of a frame in bits? (ii) What is the frame rate? (iii) What is the duration of a frame? (iv) What is the data rate?', 2, 1),
    (3, 'Draw the digital hierarchy of the telephone network. Show data rate in each line.', 2, 1),
    (3, 'A switching system uses statistical time-division multiplexing to handle traffic from 18 terminals, each generating data at an average rate of 100 characters per second with a maximum burst rate of 500 characters per second. Each character is 8 bits. The link between the multiplexer and demultiplexer has a capacity of 56 kbps. (i) Calculate the multiplexing efficiency compared to conventional TDM. (ii) If the multiplexer uses a 6-bit address field, what is the overhead percentage? (iii) Determine the maximum delay for a packet during peak traffic conditions.', 2, 1),
    (3, 'Explain with an example the importance of power management in CDMA system.', 2, 1),
    (3, 'A CRC-16 generator polynomial G(x) = x^16 + x^15 + x^2 + 1 is used for error detection. (i) Show how the bit pattern 11010011101100 would be encoded. (ii) If the transmitted bit pattern is received as 11010011101100000100000010001, determine if there is an error. (iii) Calculate the probability that the CRC will fail to detect a burst error of length 17 bits. (iv) Explain how the CRC''s error detection capability changes if the message length increases.', 3, 1),
    (3, 'A (15,11) Hamming code is used for error correction. (i) Calculate the Hamming distance of this code. (ii) If the code is modified to detect double errors by adding an overall parity bit, what would be the new code parameters and its error detection capability?', 1, 1),
    (3, 'Explain threshold decoding of a convolution code with a suitable example.', 5, 1),
    (3, 'Generate a trellis diagram for the following convolution encoder. What will be the code for the input stream 11011? Explain how to decode the received message 111111001111001 to correct the errors using Viterbi algorithm.', 5, 1),
    (3, 'An image is 1024 x 768 pixels with 2 bytes/pixel. Assume the image is uncompressed. How long does it take to transmit it over a 56-kbps modem channel, over a 1-Mbps cable modem, over 100-Mbps Ethernet, and over Gigabit Ethernet?', 2, 1),
    (3, 'A system has an n-layer protocol hierarchy. Applications generate messages of length m bytes. At each of the layers, an h-byte header is added. What fraction of the network bandwidth is filled with headers?', 2, 1),
    (3, 'For BPSK, Eb/No = 7.5 dB is required for a BER of 10^-3. If the effective noise temperature is 27°C and the data rate is 36 kbps, what received signal level is required?', 2, 1),
    (3, 'Why should PCM be preferable to DM for encoding analog signals that represent digital data?', 2, 1),
    (3, 'Which ISO/OSI layer is called true end-to-end layer? Why?', 2, 1),
    (3, 'Draw ISO/OSI reference model. A WAN is designed using ISO/OSI reference model utilizing TCP/IP protocol stack. Show the protocol maps for each layer and provide the reason for choosing these protocols.', 5, 1),
    (3, 'Suppose the algorithms used to implement the operations at layer n is changed. How does this impact operations at layers n-1 and n+1?', 5, 1),
    (3, 'Suppose that a digitized TV picture is to be transmitted from a source that uses a matrix of 480 x 500 picture elements (pixels), where each pixel can take on one of 32 intensity values. Assume that 30 pictures are sent per second. Find the source data rate. Assume that the TV picture is to be transmitted over a channel with 4.5 MHz bandwidth and a 35 dB signal-to-noise ratio. Find the capacity of the channel.', 5, 1),
    (3, 'Consider two periodic functions f1(t) and f2(t), with periods T1 and T2, respectively. Is it always the case that the function f(t) = f1(t) + f2(t) is periodic? If so, demonstrate this fact. If not, under what conditions is f(t) periodic?', 5, 1),
    (3, 'In medical digital radiology, ultrasound studies consist of about 25 images extracted from a full-motion ultrasound examination. Each image consists of 512 x 512 pixels, each with 8 bits of intensity information. How many bits are there in the 25 images? Doctors would like to use 512 x 512 8-bit frames at 30 fps. Ignoring possible compression and overhead factors, what is the minimum channel capacity required to sustain this full-motion ultrasound? Suppose each full-motion study consists of 25 seconds of frames. How many bytes of storage would be needed to store a single study in uncompressed form?', 5, 1),
    (3, 'Consider an audio signal with spectral components in the range 300 to 3600 Hz. Assume that a sampling rate of 7000 samples per second will be used to generate a PCM signal. For SNR = 30 dB, what is the number of uniform quantized levels needed? What data rate is required?', 5, 1),
    (3, 'Draw the pulse stream for the following encoding schemes for the input bit stream 01001010000011101. Also mention the bit representation and synchronization methods for each scheme: NRZ-L, NRZ-I, RZ, Manchester, Differential Manchester, AMI, HDB3.', 5, 1),
    (3, 'A 6 km-long cable has an attenuation of -0.25 dB/km. A signal with a power of 2.84 mW has been received at the end of the cable. Find out the power at which the signal was transmitted from the source.', 5, 1),
    (3, 'In a transmission system data to be transmitted using modem with 14400 bps. What is the minimum bandwidth required with the following modulation methods: 4-ASK, 4-FSK, 8-PSK, 16-QAM?', 5, 1),
    (3, 'Find the minimum Eb/No required to achieve a spectral efficiency of 25 bps/Hz.', 5, 1),
    (3, 'Draw a table showing the comparison of downstream data rate, upstream data rate, baud rate and modulation scheme for the following modems: V-22bis, V-32, V-32bis, V-90, V-92. Provide any 5 AT commands that can be used with Hayes-compatible modems.', 2, 1),
    (3, 'Generate code tree and provide the Huffman code for the following characters and generate the code for each character. Character: a, b, c, d, e, f. Frequency: 45, 13, 12, 16, 9, 5.', 2, 1),
    (3, 'Ten sources, six with a bit rate of 200 kbps and four with a bit rate of 400 kbps, are to be combined using multilevel TDM with no synchronizing bits. Draw the multiplexing scheme. Answer the following with the final stage of the multiplexing: What is the size of a frame in bits? What is the frame rate? What is the duration of a frame? What is the data rate?', 5, 1),
    (3, 'Draw a digital hierarchy of the telephone network. Show data rate in each line.', 5, 1),
    (3, 'A switching system uses statistical time-division multiplexing to handle traffic from 18 terminals, each generating data at an average rate of 100 characters per second with a maximum burst rate of 500 characters per second. Each character is 8 bits. The link between the multiplexer and demultiplexer has a capacity of 56 kbps. Calculate the multiplexing efficiency compared to conventional TDM. If the multiplexer uses a 6-bit address field, what is the overhead percentage? Determine the maximum delay for a packet during peak traffic conditions.', 5, 1),
    (3, 'Consider a system implementing Selective Repeat ARQ with the following parameters: Bandwidth: 100 Mbps, Distance between sender and receiver: 3000 km, Propagation speed: 2 x 10^8 m/s, Frame size: 1500 bytes, ACK size: 50 bytes, Link error probability: 10^-4, Maximum window size: 127 frames. Calculate the optimal window size for maximum channel utilisation. Determine the maximum achievable channel utilisation. If the sequence number field is 8 bits, is it sufficient for this scenario? Justify your answer.', 5, 1),
    (3, 'A CRC-16 generator polynomial G(x) = x^16 + x^15 + x^2 + 1 is used for error detection. Show how the bit pattern 11010011101100 would be encoded. If the transmitted bit pattern is received as 11010011101100000100010000010001, determine if there is an error. Calculate the probability that the CRC will fail to detect a burst error of length 17 bits. Explain how the CRC''s error detection capability changes if the message length increases.', 5, 1),
    (3, 'A (15,11) Hamming code is used for error correction. Calculate the Hamming distance of this code. If the code is modified to detect double errors by adding an overall parity bit, what would be the new code parameters and its error detection capability?', 5, 1),
    (3, 'Explain threshold decoding of convolution code with a suitable example.', 5, 1),
    (3, 'Generate a convolution code tree for the given encoder for 5-bit input data. What will be the code for the input stream 11011? How to sequentially decode the received message 111111001111001 to correct the errors.', 5, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 4: Data Structures and Algorithms (Semester 3)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (4, 'What is the concept of a priority Queue? What are the two types? Explain.', 4, 1),
    (4, 'Differentiate between linear and Circular Queue. In an array implementation of Circular Queue, how do you find whether: Queue is empty or not; Queue is full or not; Number of elements present.', 4, 1),
    (4, 'Represent a polynomial in one variable to a doubly linked list using a suitable node structure (e.g. 7x^5 - 6x^3 + 4x^2 + 9). Develop an algorithm to add two such polynomials.', 4, 1),
    (4, 'Give the code sequences for push() and pop() operations when a stack is implemented with a Singly linked list.', 4, 1),
    (4, 'What is a circular list? Write the code sequence for finding the number of elements in it.', 4, 1),
    (4, 'Implement a DLL and do the following operations: Rotate the list left by n positions; Swap the adjacent nodes and create the new list. Write a program to input a decimal number, convert to binary and digits should be stored in a Singly linked list. Print the binary equivalent by reading the linked list.', 10, 1),
    (4, 'Write Infix to Prefix Algorithm. Using the Algorithm convert the following expression to prefix: (M + N - P) + Q/R/S + ((T * U)/V).', 10, 1),
    (4, 'Create a doubly linked list containing two digit numbers with a checksum in the last node (checksum is the sum of all numbers with sum adjusted to two digits by ignoring the carry). Admin user has the provision to add/delete new data to the list and modify the checksum accordingly. Ordinary users can only verify permission to check if the checksum is correct or not. Model the whole system.', 10, 1),
    (4, 'Write a suitable algorithm for the postfix evaluation. Feed the following infix expression in postfix form to the algorithm, evaluate by showing the intermediate stack contents each time: 2*(5*(3+6))/15 - 2.', 10, 1),
    (4, 'Implement a Toll booth with collection counters separated for Cars, Buses and Large trucks. When a vehicle approaches, it is to be assigned a counter number according to the category. At the counter, toll should be collected as per the order in which it arrives, except for ambulance, police or fireforce vehicles. At the end of the day, total collections must be tabulated category wise and also total number of vehicles passed through that particular counter on that day. Use an appropriate data structure for the implementation.', 10, 1),
    (4, 'Derive and find the expressions for maximum number of leaves and non leaves in a binary tree of depth T.', 4, 1),
    (4, 'Convert the following expression to an expression tree: (5+3)*7^8 - 9 + 6*2. Find the postfix equivalent from the tree.', 4, 1),
    (4, 'Represent the given binary tree using implicit array representation. For a node at position p, its left child is located at __ and right child at __.', 4, 1),
    (4, 'Differentiate between B tree and B+-trees. Why are B+ trees considered more suitable for Queries?', 4, 1),
    (4, 'Write the code sequence for deleting a node with one child in a binary search tree.', 4, 1),
    (4, 'What are threads? Write a routine for doing threaded inorder traversal. Illustrate with a suitable example. Write a Java program to create a binary search tree of given numbers. Given the value of two nodes, check whether they are siblings or not.', 10, 1),
    (4, 'Write a Java program to input two integers low and high. Output the sum of values from the tree of all nodes having values in the range low to high (Assume the tree is existing). Insert the following values step by step into a B-tree of order 3: 35, 5, 20, 7, 20, 15, 30, 32, 25, 50. After building the tree explain the process of deletion of 32.', 10, 1),
    (4, 'How do you check whether the tree given is an AVL tree or not? If not, what are the rotations applied? Create an AVL tree using the following set of numbers: 30, 20, 50, 25, 22, 18, 15, 18, 100, 60, 150.', 10, 1),
    (4, 'What are the advantages of using linked lists over arrays?', 2, 1),
    (4, 'Describe the structure of a doubly linked list.', 2, 1),
    (4, 'Explain the differences between a binary tree and a binary search tree.', 2, 1),
    (4, 'Define spanning trees. Explain the concept of a minimum spanning tree.', 2, 1),
    (4, 'Define hashing, hash tables and hash function.', 2, 1),
    (4, 'Evaluate the postfix expression 5 6 2 + * 12 4 /- using a stack. Show the steps and the contents of the stack at each stage.', 5, 1),
    (4, 'Explain the implementation of stacks using arrays and linked lists. Provide algorithms for push, pop and peek operations.', 5, 1),
    (4, 'Describe linear and circular queues. Explain their implementations, operations (enqueue, dequeue) and applications.', 5, 1),
    (4, 'Convert the infix expression to postfix using the stack method. Show the step-by-step working of the stack: (A + B) * (C ^ D - E) / (F + G).', 5, 1),
    (4, 'Insert the elements 50, 30, 70, 20, 40, 60, 80, 10 into a Binary Search Tree (BST). Show the tree after each insertion and perform an in-order traversal. Write the algorithm to delete the node containing 10. Write the algorithm to delete the node containing 50.', 5, 1),
    (4, 'Given the expression (A + B) * (C - D) / E, construct its expression tree. Perform pre-order, in-order and post-order traversals on the tree.', 5, 1),
    (4, 'Describe AVL trees and their balancing mechanisms. Construct an AVL tree by inserting the following sequence of elements 6, 7, 9, 4, 3, 5, 8 step-by-step. Show the resultant tree after each insertion. Write the techniques for LL, LR, RL and RR rotations.', 10, 1),
    (4, 'Describe Kruskal''s and Prim''s algorithms for finding the minimum spanning tree. Explain how each algorithm works on the given graph.', 10, 1),
    (4, 'Implement the Depth-First Search (DFS) and Breadth-First Search (BFS) algorithms on the given graph starting from vertex e. Write adjacency list and adjacency matrix for the given graph.', 10, 1),
    (4, 'Explain merge sort algorithm. Illustrate the working of merge sort algorithm using the following numbers: 50, 45, 16, 32, 26, 17, 35, 60.', 5, 1),
    (4, 'Write the algorithm for quick sort. Explain how you choose the pivot element. Explain the steps while sorting the following sequence of elements: 15, 40, 28, 10, 23, 58, 19, 30.', 5, 1),
    (4, 'Define collision in hashing. Explain in detail open hashing and closed hashing.', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 5: Database Management Systems (Semester 4)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (5, 'Define the following terms: (a) Functional dependency (b) Full functional dependency (c) Transitive dependency (d) Trivial functional dependency (e) Prime attributes.', 5, 1),
    (5, 'Explain various relational model constraints.', 5, 1),
    (5, 'Draw and explain 3 schema architecture.', 5, 1),
    (5, 'Write a function in SQL to find the factorial of a number.', 5, 1),
    (5, 'Consider the relation R(P, Q, R, S, T) with the following functional dependencies: P -> Q, R; Q -> S; R -> T. Determine the highest normal form (1NF, 2NF, 3NF, or BCNF) that this relation satisfies. Provide justification for your answer.', 6, 1),
    (5, 'For the given table, show the result after converting to 1NF. Student_ID: 101 (Name: John, Phone Numbers: {9876543210, 9123456780}, Address: {Street: 12A, City: New York}, Courses: {Math, Science}), Student_ID: 102 (Name: Alice, Phone Numbers: {8765432109}, Address: {Street: 34B, City: Chicago}, Courses: {English, History}).', 4, 1),
    (5, 'Given the relations: Movie (Movie_ID, Title, Genre, Release_Year, Budget), Actor (Actor_ID, Name, Gender, Nationality), Acts_In (Actor_ID, Movie_ID). Write relational algebra for: (A) List all movie titles and their release years from the Movie table. (B) Retrieve the titles and budgets of all Action movies released after 2020. (C) List the names of actors and the titles of movies they acted in. (D) Find actors who have acted in all movies. (E) List the names of actors who acted in either the movie ''Inception'' or the movie ''Interstellar'' but not in both.', 6, 1),
    (5, 'For the above tables (Movie, Actor, Acts_In), create a trigger that automatically sets the Genre of a movie to ''Classic'' if the Release_Year is before 1980.', 4, 1),
    (5, 'Given the relations: Movie (Movie_ID, Title, Genre, Release_Year, Budget), Actor (Actor_ID, Name, Gender, Nationality), Acts_In (Actor_ID, Movie_ID). (1) Create tables Movie, Actor and Acts_in with all required constraints. (2) List the names of actors along with the titles of the movies they acted. (3) Find the number of movies released each year. (4) Find the actors who have acted in more than 3 movies. (5) Retrieve the top 5 most expensive movies. (6) List the names of actors who acted in the movie with the highest budget. (7) List the names of actors who acted in both ''Inception'' and ''Interstellar''. (8) Find actor who acted in the most number of movies (without using limit).', 10, 1),
    (5, 'A film industry organization wants to design a database system to manage information about movies, actors, directors, producers, and awards. Design an ER diagram for this system.', 10, 1),
    (5, 'What is a transaction in DBMS? What are the desirable properties of a transaction?', 5, 2),
    (5, 'What is hashing? Explain external hashing technique.', 5, 1),
    (5, 'A hash table of size 10 is used with the hash function h(key) = key mod 10. Insert the following keys into the hash table: 23, 33, 43, 13, 27, 88, 97, 17, 37. Insert the keys using Open Addressing (Linear Probing). Show each step clearly. Display the final hash table. Calculate the total number of collisions.', 5, 1),
    (5, 'Explain how basic database operations are performed in heap file organization.', 5, 1),
    (5, 'Consider the following schedules with 4 transactions. Check if they are conflict serializable. If yes find the corresponding serial schedule. (a) S1: r1(X); r3(X); w1(X); r1(Z); w2(Z); r3(Y); w3(Y); r2(Y); r4(Y); w4(Y). (b) S2: r1(X); r2(Z); r3(X); r1(Z); r4(Y); w1(X); r3(Y); w3(Y); r2(Y); w2(Z); w2(Y); w4(Y).', 6, 1),
    (5, 'Why is concurrency control needed?', 4, 1),
    (5, 'A relation contains 1,00,000 records, each of size 100 bytes. The block size is 4 KB. Calculate the number of block accesses needed for multi-level indexing to search for a record if each index entry takes 16 bytes.', 5, 1),
    (5, 'Explain dynamic multilevel indexing.', 5, 1),
    (5, 'Explain various types of single level ordered index in detail.', 10, 1),
    (5, 'Define the following terms: (a) Recoverable Schedule (b) Strict Schedule (c) Serial Schedule (d) Serializable Schedule (e) Cascadeless Schedule.', 5, 1),
    (5, 'Consider three transactions T1, T2, and T3 with the following timestamps: TS(T1) = 5, TS(T2) = 12, TS(T3) = 20. Apply the Timestamp Ordering Protocol to the given schedule.', 5, 1),
    (5, 'Define Functional dependency', 1, 1),
    (5, 'Define Full functional dependency', 1, 1),
    (5, 'Define Transitive dependency', 1, 1),
    (5, 'Define Prime attributes', 1, 1),
    (5, 'Define Normalization', 1, 1),
    (5, 'What are the characteristics of Database Management Systems', 5, 1),
    (5, 'What are the various types of data, and which DBMS is used for each type?', 5, 1),
    (5, 'Write a function in SQL to find the factorial of a number', 5, 1),
    (5, 'Given a relation T(A, B, C, D, E) and Functional Dependency set FD = {AD → BCE, A → BC, C → B}. Is T in 2NF? If not decompose to 2NF. Is the resultant table in 3NF? If not decompose to 3NF.', 10, 1),
    (5, 'For the given table, show the result after converting to 1NF. Student_ID: 101 (John), Phone Numbers: {9876543210, 9123456780}, Address: {Street: 12A, City: New York}, Courses: {Math, Science}. Student_ID: 102 (Alice), Phone Numbers: {8765432109}, Address: {Street: 34B, City: Chicago}, Courses: {English, History}.', 10, 1),
    (5, 'Explain various relational model constraints', 10, 1),
    (5, 'For Customers (ID, Name, Email, City) and Orders (Order ID, Customer ID, Product, Quantity, Price, Order Date), write relational algebra for: Display names of all customers from Mumbai; Display names of products purchased by customers from Mumbai; Display products that are purchased by all customers.', 10, 1),
    (5, 'For Customers (ID, Name, Email, City) and Orders (Order ID, Customer ID, Product, Quantity, Price, Order Date): Create tables Customers and Orders with all required constraints; Insert at least 2 rows into the tables; Retrieve details of orders with price greater than 100; Retrieve details of all orders with details of customers who purchased them; Find total revenue for each product; Display names of customers who placed more than 1 order; Find the Most Expensive Order; Delete Orders Where Quantity is Less Than 2.', 10, 1),
    (5, 'For the table Employee (EmpID, EmpName, Salary), create a cursor in SQL that processes each employee''s salary, calculates their income tax, determines the net salary, creates a new table Employee_salary_details and inserts all details.', 10, 1),
    (5, 'Differentiate between fixed length and variable length records', 5, 1),
    (5, 'Differentiate between spanned and un-spanned records', 5, 1),
    (5, 'Describe the structural constraints in relationships. Give examples.', 5, 1),
    (5, 'Consider an EMPLOYEE file with 10000 records where each record is of size 80 bytes. Compute the number of block accesses for Multi-level primary index.', 5, 1),
    (5, 'Consider the following schedules with 4 transactions on 3 data elements. Check if they are conflict serializable. S1: r1(A), w1(B), r2(B), w2(C), r3(A), w3(C), r4(C), w4(A). S2: r1(A), w1(B), r2(B), w2(C), r3(C), w3(A), r4(A), w4(C), r1(C), r2(A), r3(B).', 10, 1),
    (5, 'Determine whether each schedule is strict, cascadeless, recoverable, or nonrecoverable. S3: r1(X); r2(Z); r1(Z); r3(X); r3(Y); w1(X); c1; w3(Y); c3; r2(Y); w2(Z); w2(Y); c2. S4: r1(X); r2(Z); r1(Z); r3(X); r3(Y); w1(X); w3(Y); r2(Y); w2(Z); w2(Y); c1; c2; c3.', 10, 1),
    (5, 'What is collision in Hashing? Explain the various techniques for collision resolution.', 10, 1),
    (5, 'Explain how secondary indexes are used on key and non-key field', 10, 1),
    (5, 'Explain files of unordered and ordered type. How are they different in terms of performing insertion, deletion, searching and modification on records.', 10, 1),
    (5, 'Design an E-R diagram for an online food delivery system.', 10, 1),
    (5, 'What are the constraints on specialization and generalization?', 2, 1),
    (5, 'Describe record blocking and the process of calculating the blocking factor.', 2, 1),
    (5, 'Differentiate between trivial and non-trivial functional dependencies; partial and full functional dependencies.', 2, 1),
    (5, 'Describe the desirable properties of a transaction.', 2, 1),
    (5, 'How do sharding and replication differ in MongoDB?', 2, 1),
    (5, 'Design an ER diagram for a cricket management system with four main entities, including a derived attribute.', 10, 1),
    (5, 'Illustrate three schema architecture and explain how it achieves the various characteristics of DBMS.', 10, 1),
    (5, 'Explain the different types of single level ordered indexes in detail.', 10, 1),
    (5, 'For Patients (PatientID, Name, Age, Gender, Disease), Doctors (DoctorID, Name, Specialization), Appointments (AppointmentID, PatientID, DoctorID, FeesPaid, AppointmentDate_Time): Write SQL queries and create a trigger for the hospital management system.', 10, 1),
    (5, 'For Patients, Doctors, and Appointments tables, write relational algebra queries and determine the highest normal form for the given relation.', 10, 1),
    (5, 'What is timestamp ordering? Explain the Two-Phase Locking (2PL) protocol and how it ensures serializability.', 10, 1),
    (5, 'Consider T1: r1(X); r1(Z); w1(X). T2: r2(Z); r2(Y); w2(Z); w2(Y). T3: r3(X); r3(Y); w3(Y). Draw the serializability graphs for S1 and S2 and state whether each schedule is serializable.', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 6: Differential Equations and Complex Variables (Semester 3)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (6, 'Find the Residue of f(z) = z^2 / ((z-1)^2(z+2)) at each pole.', 2, 1),
    (6, 'Form Partial Differential Equation from z = f(x + iy) + g(x - iy).', 2, 1),
    (6, 'Solve p + q = z.', 2, 1),
    (6, 'Solve p√x + q√y = √z.', 2, 1),
    (6, 'State Cauchy''s Residue Theorem.', 2, 1),
    (6, 'Using Residue Theorem, evaluate the integral of e^z / (z^2 + π^2) dz where C is the circle |z| = 4.', 5, 1),
    (6, 'Evaluate the integral from 0 to 2π of dθ/(2 + cosθ).', 5, 1),
    (6, 'Solve x^2p^2 + y^2q^2 = z^2.', 6, 1),
    (6, 'Form the Partial Differential Equation of (x-a)^2 + (y-b)^2 + z^2 = c^2.', 4, 1),
    (6, 'Solve x(y-z)p + y(z-x)q = z(x-y).', 5, 1),
    (6, 'Solve r + s - 6t = cos(2x + y).', 5, 1),
    (6, 'Using Cauchy''s Residue theorem, evaluate the integral of (3z^2+z-1)/((z^2-1)(z-3)) dz where C is the circle |z| = 2.', 5, 1),
    (6, 'Evaluate the integral from 0 to π of (1 + cosθ)/(5 + 3cosθ) dθ using Cauchy''s Residue Theorem.', 5, 1),
    (6, 'Solve the equation ∂u/∂x = 2∂u/∂t + u, given u(x, 0) = 6e^(-3x).', 5, 1),
    (6, 'Solve (D^2 + DD'' - 6D''^2)z = y cos x.', 5, 1),
    (6, 'Test whether the function f(z) = xy + iy is analytic or not.', 2, 1),
    (6, 'Show that the transformation w = 1/z transforms all circles and straight lines into circles and straight lines in W-plane.', 2, 1),
    (6, 'State Cauchy''s Integral Theorem.', 2, 1),
    (6, 'Find the analytic function whose imaginary part is 3x^2y - y^3.', 2, 1),
    (6, 'Evaluate the integral from (0,0) to (2+i) of (x^2 - iy) dz along the path y = x^2.', 2, 1),
    (6, 'Show that the function f(z) = √|xy| is not regular at the origin, although C-R equations are satisfied. Find the analytic function whose real part is e^(-x)(x sin y - y cos y).', 10, 1),
    (6, 'State and Prove Cauchy''s Integral Formula. Verify Cauchy''s Theorem for f(z) = z + 1 taken over the boundary of a square with vertices 0, 1, 1+i, i.', 10, 1),
    (6, 'Find the bilinear transformation which maps the points z = 2, i, -2 into the points w = -1, i, 1. Find the analytic function f(z), if u + v = x/(x^2+y^2) and f(1) = 1.', 10, 1),
    (6, 'Find the image of the circle |z - 2i| = 2 in the complex plane under the mapping w = 1/z. Evaluate the integral of (z^2+5)/((z-3)) dz where C is the circle: |z| = 4; |z| = 1.', 10, 1),
    (6, 'Find the fixed points of the mapping w = (5-4z)/(4z-2).', 2, 1),
    (6, 'Expand the Laurent''s series of z^2 * e^(1/z).', 2, 1),
    (6, 'Evaluate the integral of (e^z + z^2)/(z-0.5)^2 dz where C is |z| = 1.', 2, 1),
    (6, 'Form the partial differential equation from z = f(x^2 + y^2).', 2, 1),
    (6, 'Write the various possible solutions of one-dimensional heat equation.', 2, 1),
    (6, 'Find the bilinear transformation which maps z = i, -1, 1 on to w = 0, 1, ∞.', 5, 1),
    (6, 'Describe the image of x^2 + y^2 = r^2 under the mapping w = 1/z.', 5, 1),
    (6, 'Find the analytic function f(z) = u + iv if u - v = (x + y)(x^2 - 2xy + y^2).', 5, 1),
    (6, 'Describe the image of |z - 1| = 1 under the mapping w = z^2.', 5, 1),
    (6, 'Evaluate the integral from 0 to 2π of dθ/(5-3cosθ) using contour integration.', 5, 1),
    (6, 'Evaluate the integral of (z^6+1)/(z^2(2z-1)(z-2)) dz where C is |z| = 1 using residue theorem.', 5, 1),
    (6, 'State and prove Cauchy''s integral formula. Evaluate the integral of e^z/((z^2+4)(z-i)^2) dz where C is |z-i| = 1/2.', 10, 1),
    (6, 'Solve z(p^2 - q^2) = x - y. Solve ∂^3z/∂x^3 - 4∂^3z/∂x^2∂y + 4∂^3z/∂x∂y^2 = 4sin(2x+y).', 10, 1),
    (6, 'Find the integral surface of x(y^2+z)p - y(x^2+z)q = z(x^2-y^2). Solve ∂u/∂x = 2∂u/∂t + u, given u(x,0) = 6e^(-3x).', 10, 1),
    (6, 'Derive one dimensional heat equation. Derive the solution of one-dimensional wave equation.', 10, 1),
    (6, 'An infinite long plate is bounded by two parallel edges and an edge at right angles to them. Determine the temperature at any point on the plate in steady state.', 5, 1),
    (6, 'The vibration of a string is given by ∂^2u/∂t^2 = c^2 ∂^2u/∂x^2. Find the displacement of any point at a distance x from one end at time t.', 5, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 7: Discrete Computational Structures (Semester 3)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (7, 'Let p, q, r denote the following statements about particular triangle ABC. p: Triangle ABC is isosceles; q: Triangle ABC is equilateral; r: Triangle ABC is equiangular. Translate each of the following into an English sentence: q → p; ~p → ~q; q ↔ r; p ∧ ~q; r → p.', 4, 1),
    (7, 'Write the converse, inverse, and contrapositive of each of the following implications. Give primitive statements p, q, r, show that the implication [(p v q) ∧ (~p v r)] → (q v r) is a tautology.', 4, 1),
    (7, 'Write the following statements in symbolic form: At least one integer is even; There exists a positive integer that is even; If x is even, then x is not divisible by 5; No even integer is divisible by 5.', 4, 1),
    (7, 'For A = {1, 2, 3, 4, 5, 6, 7} determine the number of: Subsets of A; Nonempty subsets of A; Proper subsets of A; Subsets of A containing five elements, including 1, 2.', 4, 1),
    (7, 'A professor has seven different programming books on a book shelf. Three deal with C++, the other four with Java. In how many ways can the professor arrange these books? In how many ways can the letters in DATAGRAM be arranged?', 4, 1),
    (7, 'Without constructing truth tables prove the following: ~p → (q → r) ≡ q → (p v r). Negate and simplify each of the following: ∃x[p(x) v q(x)]; ∀x[p(x) ∧ ~q(x)].', 10, 1),
    (7, 'Explain the Principle of Mathematical Induction. Prove: 1.3 + 2.4 + ... + n(n+2) = [n(n+1)(2n+7)]/6.', 10, 1),
    (7, 'Define one-to-one function. For each of the following functions, determine whether it is one-to-one and determine its range: f: Z → Z, f(x) = 2x+1; f: Q → Q, f(x) = 2x+1; f: Z → Z, f(x) = x^2 - x; f: Z → Z, f(x) = e^x.', 10, 1),
    (7, 'Define Equivalence relation. Which of the following is an equivalence relation? The relation R on Z defined by aRb if a^2 - b^2 ≤ 7; The relation R on Z defined by aRb if a^2 - b^2 = 0.', 10, 1),
    (7, 'For each of the following degree sequences, find if there exists a graph: 5, 5, 4, 3, 2, 1; 3, 3, 3, 3, 3, 3. Prove that K4 is planar.', 4, 1),
    (7, 'Solve the recurrence relation a_n - 7a_{n-1} + 10a_{n-2} = 0, a_0 = 0, a_1 = 6.', 4, 1),
    (7, 'Write down the conditions for graph isomorphism. Determine whether the given figures are isomorphic.', 4, 1),
    (7, 'Which of the simple graphs have a Hamilton circuit or, if not, a Hamilton path? Write the reason.', 4, 1),
    (7, 'Define n-regular graph. Give one example for each of 2-regular and 3-regular graphs.', 4, 1),
    (7, 'Find the number of vertices, the number of edges, and the degree of each vertex in the given undirected graphs. Verify also the Handshaking theorem in each case.', 4, 1),
    (7, 'Represent the given graphs by adjacency matrices. Draw the graph represented by the given incidence matrices.', 10, 1),
    (7, 'Define a binary tree when it is called a full binary tree. Sketch the 13 vertex binary tree with minimum and maximum height. Also find the path length of both trees.', 10, 1),
    (7, 'State the necessary and sufficient condition for the existence of an Eulerian path in a connected graph. By applying Fleury''s Algorithm, find the Euler Cycle in the given graph.', 10, 1),
    (7, 'State Kruskal''s algorithm and using Kruskal''s algorithm, find the minimum spanning tree of the given Graph G.', 10, 1),
    (7, 'Construct the truth table for p ↔ [(q ∧ r) → ~(s v r)].', 2, 1),
    (7, 'Let R = {(1,2),(3,4),(2,2)} and S = {(4,2),(2,5),(3,1),(1,3)}. Find RoS, SoR, Ro(SoR) and RoR.', 2, 1),
    (7, 'In how many different ways can the letters of the word ''MATHEMATICS'' be arranged such that the vowels must always come together?', 2, 1),
    (7, 'Find the adjacency and incidence matrix for the given graph.', 2, 1),
    (7, 'With suitable examples define GLB and LUB of a partially ordered set.', 2, 1),
    (7, 'Without using truth table prove that (P v Q) ∧ (~P ∧ ~Q) v (~P ∧ ~R)) v (~P ∧ ~Q) v (~P ∧ ~R) is a tautology.', 10, 1),
    (7, 'Use mathematical induction to prove that 1^2 + 2^2 + 3^2 + ... + n^2 = (1/6)(n(n+1)(2n+1)) for all n ∈ N.', 10, 1),
    (7, 'Solve the recurrence relation a_n = 2a_{n-1} + 5a_{n-2} - 6a_{n-3} with a_0 = 7, a_1 = -4 and a_2 = 8.', 10, 1),
    (7, 'Solve the recurrence relation a_n = -6a_{n-1} - 9a_{n-2} for n ≥ 2, a_0 = 3, a_1 = -3. State Pigeonhole principle. Show that among any 4 numbers one can find 2 numbers so that their difference is divisible by 3.', 10, 1),
    (7, 'State Fleury''s algorithm. Use the algorithm to construct an Euler circuit for the given graph. Let G = (V, E) is a connected graph with |E| = 17 and deg(v) ≥ 3 for all v ∈ V. What is the maximum value for |V|?', 10, 1),
    (7, 'Explain the minimum spanning tree (MST). Construct the MST for the given graph using Kruskal''s Algorithm. Determine whether the given graphs are isomorphic or not. Justify your answer.', 10, 1),
    (7, 'Define the Abelian group. Prove that the algebraic structure (Q*, *) is an abelian group where * is defined on Q* by a*b = (ab)/2. Draw the Hasse diagram for the given lattice.', 10, 1),
    (7, 'Define a Lattice. Consider the set D50 = {1, 2, 5, 10, 25, 50} with the divides relation. Draw the Hasse diagram of D50. Determine all upper bounds of 5 and 10. Determine all lower bounds of 5 and 10.', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 8: Microprocessors (Semester 4)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (8, 'How many T-states and machine cycles are required to execute the instruction SHLD 3050H?', 4, 1),
    (8, 'During the execution of a program, a hardware device produces a very short pulse (which may be maskable if required) that must not be missed by the 8085 microprocessor. Which interrupt line would you select for this situation? Justify your answer.', 4, 1),
    (8, 'Assume the memory location 2000H contains 05H. Predict the contents of register A after execution of the following program: LXI H, 2000H / MOV A, M / DCR A / JZ SKIP / INR A / SKIP: HLT', 4, 1),
    (8, 'Explain the different addressing modes of the 8086 microprocessor with suitable examples for each.', 4, 1),
    (8, 'With suitable diagrams, explain the internal working of PUSH B and POP B instructions in 8085.', 10, 1),
    (8, 'A temperature monitoring system using the 8085 stores 10 sensor readings in consecutive memory locations starting from 3000H. Write the complete 8085 assembly program to read each value, add them, and store the final sum in memory location 4000H.', 10, 1),
    (8, 'Draw the block diagram of the 8086 microprocessor and explain its components and working.', 10, 1),
    (8, 'Differentiate between 8086 and 8088 microprocessors.', 4, 1),
    (8, 'Explain DOS interrupts.', 4, 1),
    (8, 'Describe the interfacing of flash memory with a microprocessor.', 4, 1),
    (8, 'Write about the working of the 8259 interrupt controller.', 4, 1),
    (8, 'Explain the assembler directives of 8086. Explain the interrupt vector table and how memory is allocated for interrupts.', 10, 1),
    (8, 'Discuss the minimum and maximum mode operations of the 8086 system with neat diagrams.', 10, 1),
    (8, 'Design the interfacing of 8255 with 8086 and explain what it is used for and its modes of operation.', 10, 1),
    (8, 'When ALE goes high, AD0-AD7 lines show 55H. After ALE goes low, AD0-AD7 shows F2H. What operation is likely taking place?', 2, 1),
    (8, 'If an 8085 system shows the following signals: IO/M = 1, RD = 0, WR = 1. What type of operation is being performed? What is the purpose of the instruction register and decoder in 8085?', 2, 1),
    (8, 'If RST 7.5 and RST 6.5 interrupts occur simultaneously, and the mask bits for both are 0 (enabled), which interrupt will be serviced first and why?', 2, 1),
    (8, 'For the instruction sequence: LXI H, 2050H; MOV M, C; INX H; MOV M, B. What addressing mode is used to store C and B registers'' contents and at what memory locations?', 2, 1),
    (8, 'After executing the following instructions: MVI A, 25H; MOV B, A; INR B. What will be the content of register B? If SP = 2050H, what will be its value and memory content after executing PUSH B?', 2, 1),
    (8, 'The 8085 microprocessor follows a specific architecture that allows efficient execution of instructions. Explain how the various components of 8085 work together to execute an instruction.', 10, 1),
    (8, 'The 8085 microprocessor supports various types of instructions categorized based on the function it performs. Explain the significance of these instruction categories with suitable examples.', 10, 1),
    (8, 'Define interrupts and explain their different types by their various categories. How does the 8085 prioritize and manage multiple interrupt requests?', 10, 1),
    (8, 'Write an assembly language program for the 8085 microprocessor to multiply two 8-bit numbers. Explain the logic of your program.', 10, 1),
    (8, 'Explain the various addressing modes used in 8085 with suitable examples.', 10, 1),
    (8, 'Describe the role and significance of the DEN (Data Enable) signal in the operation of the 8086 microprocessor.', 2, 1),
    (8, 'What is the function and significance of segment registers in the architecture of the 8086 microprocessor?', 2, 1),
    (8, 'Describe the role and functionality of the QS0 and QS1 status pins in the 8086 microprocessor architecture.', 2, 1),
    (8, 'Explain two ways in which Static RAM differs from Dynamic RAM in terms of their characteristics.', 2, 1),
    (8, 'Identify and describe the two operating modes available in the 8257 DMA controller architecture.', 2, 1),
    (8, 'Illustrate and describe the memory segmentation scheme used in the 8086 microprocessor. Also, explain the method of calculating physical addresses.', 10, 1),
    (8, 'Explain the structure and purpose of the Interrupt Vector Table in the 8086 microprocessor. How is it accessed during the execution of an interrupt?', 10, 1),
    (8, 'Discuss the addressing modes supported by the 8086 microprocessor architecture. Provide suitable instruction examples to demonstrate each mode.', 10, 1),
    (8, 'Describe the structure and working principle of the 8259 Programmable Interrupt Controller. Elaborate on its initialization, interrupt handling process, and advanced features.', 10, 1),
    (8, 'Explain the architecture and working of the 8087 math coprocessor. How does it interface with the 8086 microprocessor?', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 9: Numerical and Statistical Methods (Semester 4)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (9, 'Write a short note on error.', 2, 1),
    (9, 'Find a root of the equation 3x - cos x - 1 = 0 by Newton''s method.', 2, 1),
    (9, 'Expand log_e x in powers of (x-1) and hence evaluate log_e (1.1) correct to 4 decimal places.', 2, 1),
    (9, 'Define Least square.', 2, 1),
    (9, 'Find the real root of x^3 - x - 1 = 0 by bisection method.', 2, 1),
    (9, 'By using Secant method find a root of the equation x^3 - 2x - 5 = 0.', 5, 1),
    (9, 'The population in different years is given below. Find the population in 1955. Year: 1951, 1961, 1971, 1981. Population (in thousands): 35, 42, 58, 84.', 5, 1),
    (9, 'Find a root of the equation by iteration method: x^3 - 10x - 5 = 0.', 5, 1),
    (9, 'Fit a straight line for the data: X: 1 2 3 4 5, Y: 1 3 5 6 5.', 5, 1),
    (9, 'From the following table find f''(1.1) and f''''(1.1) by using Newton''s differentiation formula.', 5, 1),
    (9, 'Evaluate the integral from 0 to 1 of 1/(1+x) dx using Gauss three point formula.', 5, 1),
    (9, 'Dividing the range into 10 equal parts, find the approximate value of the integral from 0 to π of sin x dx by Trapezoidal rule.', 5, 1),
    (9, 'Apply Runge-Kutta method of fourth order to calculate the value for x = 0.2 when dy/dx = x + 4y, y(0) = 0, taking h = 0.1.', 5, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 10: Numerical and Statistical Techniques (Semester 4)
-- Merged from original subjects 12 (NST Sem4 CSE) and 19 (NST Sem4 ECE)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (10, 'Differentiate between discrete random variable and continuous random variable.', 2, 1),
    (10, 'Ten coins are tossed simultaneously. Find the probability of getting at least 7 heads.', 2, 1),
    (10, 'If X is a random variable with mean -3 and variance 4, find p(x > -1.5).', 2, 1),
    (10, 'Define skewness and kurtosis.', 2, 1),
    (10, 'Define correlation.', 2, 1),
    (10, 'In a binomial distribution consistency of 6 independent trials, probabilities of 1 and 2 successes are 0.28336 and 0.0506. Find the parameter of distribution.', 5, 1),
    (10, '8 unbiased coins were tossed simultaneously. Find the probability of getting: (i) Exactly 4 heads (ii) No heads at all (iii) 6 or more heads (iv) At most 2 heads (v) Number of heads ranging from 3 to 5.', 5, 1),
    (10, 'A random sample of 200 tins of coconut oil gave an average weight of 4.95 kgs with a standard deviation of 0.21 kgs. Do we accept this tins not weight 5 kgs per tin at 1% level?', 5, 1),
    (10, 'The standard deviation of 2 samples of size 10 and 14 from 2 normal populations are 3.5 and 3 respectively. Examine whether standard deviation of population are equal.', 5, 1),
    (10, 'Compute Karl Pearson''s coefficient of correlation for the following data. X: 2 3 4 5 6 7 8. Y: 4 5 6 12 9 5 4.', 5, 1),
    (10, 'In a competition 2 judges gave the following ranks to 8 participants. Calculate the coefficient of rank correlation.', 5, 1),
    (10, 'Find out the coefficient of correlation between x and y by method of rank differences.', 5, 1),
    (10, 'Calculate the regression equations of X on Y for the following data. X: 1 2 3 4 5. Y: 2 5 3 8 7.', 5, 1),
    (10, 'A bank record transactions are either original or fraud. Find the probability mass function of X, Mean, Variance and Standard Deviation.', 4, 1),
    (10, 'A random sample of size 12 is taken from a normal population with N(μ,3). Find the probability that variance of the sample lies between 3.4 and 14.8.', 4, 1),
    (10, 'A trucking firm is suspicious of the claim that the average lifetime of certain tyres is at least 28000 miles. What can it conclude if α = 0.01?', 4, 1),
    (10, 'Using Spearman''s Rank Correlation Method, find the coefficient of correlation for the following data: x: 12, 17, 22, 27, 31; y: 113, 119, 117, 115, 121.', 4, 1),
    (10, 'What are the Measures of Dispersion in Descriptive Statistics? Write the formula for each measure.', 4, 1),
    (10, 'A sample of 100 dry battery cells tested to find the length of life produce the following results: x̄ = 12 hrs and σ = 3 hrs. What percentage of battery cells are expected to have more than 15 hrs and less than 6 hrs?', 5, 1),
    (10, 'Derive the Mean and Variance of Binomial Distribution.', 5, 1),
    (10, 'Intelligence test of two groups of boys and girls gives the following results: Girls Mean=84, SD=10, N=121; Boys Mean=81, SD=12, N=81. Is the difference of Mean scores significant?', 5, 1),
    (10, 'The standard deviation of a sample of size 50 is 3.6. Examine whether the sample was taken from population with SD 3.3 at α = 1%.', 5, 1),
    (10, 'The following table gives the number of units produced per day by two workers A and B. Should these results be accepted as evidence that the two workers are equally stable?', 5, 1),
    (10, 'Find the correlation co-efficient and equations of regression lines for the following values of x and y: x: 1, 2, 3, 4, 5; y: 2, 5, 3, 8, 7.', 5, 1),
    (10, 'Fit a Poisson distribution to the following data and test for its goodness of fit at 5% level of significance: x: 0, 1, 2, 3, 4; f: 419, 352, 154, 56, 9.', 5, 1),
    (10, 'Find the mean of variables x and y and the correlation coefficient, given: Regression of y on x is 2y - x - 50 = 0; Regression of x on y is 3y - 2x - 10 = 0.', 5, 1),
    (10, 'Find a positive value of cube root of 5 using Newton Raphson method correct to 2 decimal places.', 2, 2),
    (10, 'Apply Simpson''s 1/3 rd rule to evaluate the integral of dx/(1+x^2) from 0 to 1 taking step size h = 0.1.', 2, 2),
    (10, 'Derive the mean and variance of binomial distribution.', 2, 2),
    (10, 'Describe the terms level of significance and power of test in testing of hypothesis.', 2, 2),
    (10, 'What are the two measures of shapes in descriptive statistics? Explain.', 2, 2),
    (10, 'Find a real root of the equation x^3 - x^2 - 1 = 0 using secant method correct to 3 decimal places.', 5, 2),
    (10, 'Use Newton''s divided difference formula to find f(4) if f(0) = 2, f(1) = 3, f(2) = 12 and f(5) = 147.', 5, 2),
    (10, 'Solve the system of equations using Gauss Seidel Method: 5x + 2y + z = 12, x + 4y + 2z = 15 and x + 2y + 5z = 20.', 5, 2),
    (10, 'Fit a second degree curve to the following data and find the value of y when x = 7: x: 1, 2, 3, 4, 5; y: 7, 25, 53, 91, 139.', 5, 2),
    (10, 'Explain the bisection method to solve a transcendental equation.', 2, 1),
    (10, 'Derive Newton Raphson formula to find cubic root of N.', 2, 1),
    (10, 'Using Lagrange''s formula find f(4) from the following data: x: 1, 3, 8, 10; f(x): 5, 25, 30, 42.', 2, 1),
    (10, 'Derive Gauss Quadrature 2 point formula.', 2, 1),
    (10, 'Using trapezoidal rule evaluate the integral of dx/(1+x^2) from 0 to 1 taking step size h = 0.1.', 2, 1),
    (10, 'Find the real root of x^3 - x^2 - 1 = 0 using successive iteration method.', 5, 1),
    (10, 'Fit a parabola to the following data and find the value of f(x) when x = 13: x: 4, 6, 7, 12; f(x): 17, 37, 50, 145.', 5, 1),
    (10, 'Solve the following system of equations using Gauss Seidel method: 6x + 15y + 2z = 72, 27x + 6y - 2z = 85, x + y + 54z = 110.', 5, 1),
    (10, 'Evaluate using Simpson''s 1/3 rd rule and 3/8 th rule the integral of x^2/(1+x^2) from 1 to 10 taking step size h = 1.', 5, 1),
    (10, 'Apply Runge Kutta method of 4th order to find y(0.3) from the initial value problem y(0.2) = 1 and h = 0.1, dy/dx = x + y.', 5, 1),
    (10, 'Find y'' and y'''' at x = 3 and at x = 4 from the following data using Newton''s forward and backward formula for derivatives.', 5, 1),
    (10, 'Probability that a batsman scores a century is 1/3. Find the probability that he may score century in exactly 2 matches and in no matches.', 2, 1),
    (10, 'Explain the use of categorical variables in regression models.', 2, 1),
    (10, 'Consider families with 4 children. What percent of families would you expect to have 2 boys and 2 girls, at least 1 boy, no girls, at most 2 girls?', 5, 1),
    (10, 'The height of the school children of a school is normally distributed with mean 54 inches and standard deviation 12 inches. What percent of students have height between 46 and 56 inches?', 5, 1),
    (10, 'Test whether accident occurs uniformly over week days on the basis of the following information: Days: Sun, Mon, Tue, Wed, Thu, Fri, Sat; No. of accidents: 11, 13, 14, 13, 15, 14, 8.', 5, 1),
    (10, 'It is claimed that a random sample of 100 tyres with mean life 15629 KM is drawn from a population of tyres which has a mean life of 15200 KM and SD = 1248 KM. Test the validity of the claim.', 5, 1),
    (10, 'Obtain Karl Pearson''s coefficient of correlation for the following data and comment on the result obtained. Price (Rs): 11, 12, 13, 14, 15, 16, 17, 18, 19, 20; Demand (Kg): 30, 29, 29, 25, 24, 24, 24, 21, 18, 15.', 5, 1),
    (10, 'From the following data form two regression equations and calculate husband''s age when wife''s age is 19.', 5, 1),
    (10, 'Explain any 5 models used in supervised learning.', 5, 1),
    (10, 'Explain any 5 techniques used in unsupervised learning.', 5, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 11: Object Oriented Software Engineering (Semester 4)
-- Merged from original subjects 13 (OOSE Sem4) and 14 (OOSE Sem5)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (11, 'Cause effect graphing is a black box testing strategy. Is it true or false? Justify your answer with an example.', 4, 1),
    (11, 'Distinguish organizing and controlling management functions with examples.', 4, 1),
    (11, 'ISO 9002 is specifically for software companies. Justify true or false. Explain features of ISO in detail.', 4, 1),
    (11, 'Distinguish SQA, SQM and SCM. Use examples.', 4, 1),
    (11, 'Is the statement ''One of the KPA of CMM level 4 is Quality being monitored using various matrices'' true or false? Justify your answer with clearly explaining all CMM levels.', 10, 1),
    (11, 'Consider the code. Using CFG, find McCabe''s cyclomatic complexity for i=1 to 10 { if (i>4) printf("True"); Else printf("False") }', 10, 1),
    (11, 'Why we use Cause Effect Graph? Use example to demonstrate. Construct a Cause Effect Graph for the following cases: If Q>P and R<S then display okay. If A<B or C<D then display Error.', 10, 1),
    (11, 'Explain work break down structure. Construct a WBS for a project with 3 modules, 3 programmers and each module covering 1 month. Make your own assumptions.', 10, 1),
    (11, 'Discuss any three key characteristics of agile model. Use examples.', 4, 1),
    (11, 'Design any two UML behavioral models for ATM transactions.', 4, 1),
    (11, 'Analyze any two blackbox testing techniques in detail.', 4, 1),
    (11, 'Explain six sigma and its various methodologies.', 4, 1),
    (11, 'Can you suggest the best life cycle models for the following systems? A hospital software where requirements are huge and clear; An object oriented system. Compare and Contrast evolutionary and Iterative waterfall model.', 10, 1),
    (11, 'What are different types of relationships between classes? Clearly explain how OOAD helps in converting analysis classes to implementation code with examples and diagrams.', 10, 1),
    (11, 'If a proper process is followed for production, then good quality products are bound to follow automatically. Explain how ISO 9000 makes this true for software industry.', 10, 1),
    (11, 'Distinguish SQA and SQM along with their key activities.', 10, 1),
    (11, 'List the latest trends, best practices and tools in UI development.', 4, 1),
    (11, 'Distinguish directing and controlling management functions with examples.', 4, 1),
    (11, 'Explain Heuristic techniques.', 4, 1),
    (11, 'Distinguish PERT chart and Gantt chart.', 4, 1),
    (11, 'Describe in detail the various cohesion and coupling techniques with examples.', 10, 1),
    (11, 'Suggest the best architecture styles for the following systems: Development of OS; A web application. Show diagrams for each architecture style.', 10, 1),
    (11, 'Explain COCOMO model.', 10, 1),
    (11, 'Explain work break down structure. Compare and contrast the three models used to analyze and plan staffing requirements and effort in software projects.', 10, 1),
    (11, 'Prototyping is best suited for object oriented development. State true or false and justify with examples.', 4, 1),
    (11, 'Functional cohesion is the best cohesion. State true or false and justify with examples.', 4, 1),
    (11, 'Justify the statement ''SRS should be complete''. Also explain characteristics and format of a good SRS.', 4, 1),
    (11, 'Can you suggest the best life cycle models for the following systems? A library software where requirements are huge and clear; A robotic system where expertise is less. Compare and contrast evolutionary and iterative waterfall model.', 10, 1),
    (11, 'What are different notations for class diagrams including different types of relationships? Draw a class diagram for a credit card system with POS and online transactions.', 10, 1),
    (11, 'DFD is a powerful object oriented analysis tool. State true or false and substantiate with examples and diagrams. Prepare an SRS for a library information system in IEEE format.', 10, 1),
    (11, 'What are the new trends in UI/UX? Define the terms Sprint, Scrum and agility with respect to agile model. Use diagrams and examples.', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 12: Operating Systems (Semester 4)
-- Merged from original subjects 15 (Operating System) and 16 (Operating Systems)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (12, 'What is critical section? State and explain the conditions to be satisfied by a solution to the critical section problem.', 4, 1),
    (12, 'Differentiate between pre-emptive and non-preemptive scheduling with an example.', 4, 1),
    (12, 'What is the purpose of System call? Explain the working of System call.', 4, 1),
    (12, 'A counting semaphore S is initialized to 7. Then, 20 P operations and 15 V operations are performed on S. What is the final value of S?', 4, 1),
    (12, 'What is the primary cause of starvation in an operating system? Explain the techniques to prevent starvation.', 4, 1),
    (12, 'Distinguish among the following terminologies: Multiprogramming system, Multiprocessing system, Multitasking system. Write a note on the following OS structures: Layered approach, Micro kernel, Modular approach.', 10, 1),
    (12, 'Explain the different states of process and transition between them with the help of a diagram. Explain convoy effect with an example.', 10, 1),
    (12, 'Explain types of semaphore with an example.', 10, 1),
    (12, 'For the process table, determine which scheduling scheme gives the lowest average turnaround time and waiting time: Shortest Remaining First; Round Robin with Time Quantum 2; Priority Scheduling (non-pre-emptive). P1: Arrival 0, Burst 3, Priority 2. P2: Arrival 1, Burst 6, Priority 5. P3: Arrival 4, Burst 4, Priority 8. P4: Arrival 6, Burst 2, Priority 1.', 10, 1),
    (12, 'List the page placement strategy.', 4, 1),
    (12, 'Explain the types of fragmentation.', 4, 1),
    (12, 'What is a File Allocation Table (FAT)? Why is it used?', 4, 2),
    (12, 'Explain deadlock with an example.', 4, 1),
    (12, 'How can you calculate the disk access time? Explain with equation.', 4, 1),
    (12, 'Discuss the different aspects of contiguous memory allocation. Find the number of page faults for the following page reference string with 3 page frames for Optimal and LRU algorithms: 1 2 3 4 5 1 4 1 6 3 2 3.', 10, 1),
    (12, 'Given six memory partitions of 300 KB, 600 KB, 350 KB, 200 KB, 750 KB, and 125 KB (in order), how would first-fit, best-fit, and worst-fit algorithms place processes of size 115 KB, 500 KB, 358 KB, 200 KB, and 375 KB? Explain Segmentation with diagram.', 10, 1),
    (12, 'The read write head is at 32. The head is moving from 124 to 0. Requests are in the order 98, 37, 14, 124, 65, 67. How much time is required by the system for: SSTF; C-Scan; Scan; FCFS? Explain different RAID levels.', 10, 1),
    (12, 'Describe the working of DMA controller with a neat diagram. Describe the implementation of I/O buffering strategies (single, double, and circular buffering). Compare their advantages and limitations.', 10, 1),
    (12, 'What are the major activities of an operating system? What is the role of the shell and kernel in an OS?', 4, 1),
    (12, 'What is a process? What are the different states of a process and show the transition between states with a diagram?', 4, 1),
    (12, 'What is Mutual Exclusion (Mutex)? What methods are used to achieve Mutex?', 4, 1),
    (12, 'A computer has 1MB of RAM allocated in units of 8KB. What will be the size of the bitmap to keep track of the free memory?', 4, 1),
    (12, 'What is meant by physical and logical address? Consider a logical address space of eight pages of 1024 words each, mapped to physical memory of 32 frames.', 4, 1),
    (12, 'Consider the following set of processes: P1 (Burst 10, Arrival 0, Priority 3), P2 (Burst 1, Arrival 2, Priority 1), P3 (Burst 2, Arrival 3, Priority 5), P4 (Burst 1, Arrival 5, Priority 4), P5 (Burst 5, Arrival 7, Priority 2). Draw Gantt charts for FCFS, preemptive SJF, nonpreemptive priority, and RR (quantum = 2). Compute turnaround and waiting times for each.', 10, 1),
    (12, 'What is meant by critical section problem? Explain how semaphores can be used to solve the producer consumer problem.', 10, 1),
    (12, 'Explain any three page replacement algorithms and compare their performance for the reference string 7, 0, 1, 2, 3, 2, 1, 0, 7, 4, 3, 2, 0, 7.', 10, 1),
    (12, 'Given memory partitions of 100K, 500K, 200K, 300K and 600K, four processes of size 212K, 417K, 112K and 426K arrive. Show allocations using Best Fit, First Fit, Worst Fit. Which makes most efficient use of memory?', 10, 1),
    (12, 'Define a file and explain the different file attributes and operations.', 4, 1),
    (12, 'How is DMA (Direct Memory Access) performed? What are its advantages in I/O operations?', 4, 1),
    (12, 'What are the necessary conditions for a deadlock to occur? Explain briefly.', 4, 1),
    (12, 'What is a directory? Explain different directory structures used in file systems.', 4, 1),
    (12, 'Explain RAID levels and their advantages.', 4, 1),
    (12, 'Construct a Resource Allocation Graph for the given system. Identify any cycle. Determine whether the system is in a deadlock.', 10, 1),
    (12, 'A disk drive has 200 cylinders (0-199). The disk head is at cylinder 45. The pending request queue is: 99, 37, 180, 140, 24, 128, 68, 77. Compute total head movement for FCFS, SSTF, SCAN, and LOOK.', 10, 1),
    (12, 'Explain the following file allocation methods: (1) Contiguous allocation (2) Linked allocation (3) Indexed allocation.', 10, 1),
    (12, 'Consider a system with 5 processes (P0-P4) and 3 resource types (A, B, C). Apply the Banker''s Algorithm to determine if the system is in a safe state and if process P2 request can be granted.', 10, 1),
    (12, 'Describe the differences among short-term, medium-term and long-term schedulers.', 2, 1),
    (12, 'How is segmentation different from paging?', 2, 1),
    (12, 'Differentiate internal fragmentation and external fragmentation.', 2, 1),
    (12, 'Define the strict two-phase locking protocol.', 2, 1),
    (12, 'Draw the Gantt Chart, find the average waiting time for FCFS, Preemptive priority, Non-preemptive priority. P1: Arrival=0, Burst=8, Priority=4. P2: Arrival=2, Burst=6, Priority=1. P3: Arrival=2, Burst=1, Priority=2. P4: Arrival=1, Burst=9, Priority=2. P5: Arrival=3, Burst=3, Priority=3.', 6, 1),
    (12, 'Describe how semaphores can be used as a synchronisation mechanism.', 6, 1),
    (12, 'With the help of a diagram, explain how a multilevel feedback queue scheduling works.', 4, 1),
    (12, 'Discuss the different aspects of contiguous memory allocation.', 5, 1),
    (12, 'With a diagram, explain how paging is done with TLB. Find the number of page faults for page reference string 2 3 4 2 1 3 7 5 4 3 with three page frames using optimal and LRU algorithms.', 10, 1),
    (12, 'The read write head is at 97. The head is moving from 299 to 0. Requests are in the order 94, 82, 101, 110, 198, 75, 87, 124, 136. How much time is required for SSTF and C-Scan?', 5, 1),
    (12, 'Describe the implementation of RAID systems. Explain different RAID levels with their advantages and disadvantages.', 5, 1),
    (12, 'Discuss how resource allocation graphs can be used to represent deadlock situations. Illustrate with examples showing both deadlock and no-deadlock cases.', 5, 1),
    (12, 'Explain the banker''s algorithm for deadlock avoidance with a step-by-step example.', 5, 1),
    (12, 'Explain two-phase locking protocol in detail. Discuss how it ensures serializability in database transactions.', 5, 1),
    (12, 'Explain various deadlock recovery techniques in detail.', 5, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 13: Principles of Programming Languages (Semester 3)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (13, 'What is an ambiguous grammar? Check whether the following grammar is ambiguous or not: S --> A; A --> A + A / id; id --> a/b/c.', 5, 1),
    (13, 'Some programming languages are typeless. What are the advantages and disadvantages of having no types in a language? Also write why dynamic type binding is closely related to implicit heap dynamic variable.', 5, 1),
    (13, 'What are the different attributes associated to a variable? Explain with example.', 5, 1),
    (13, 'What are the primary design issues for names in programming languages? Also suggest some possible solutions for the said issues.', 5, 1),
    (13, 'Write an evaluation of C++ programming language using the different language evaluation criteria.', 10, 1),
    (13, 'What is attribute grammar? Write the formal definition of finding different attributes in attribute grammar with suitable examples. Write how type combatability in assignment statement is verified using attribute grammar.', 10, 1),
    (13, 'What is binding? Discuss any five types of binding times with simple C program statements.', 10, 1),
    (13, 'Discuss the classification of variables according to their life time.', 10, 1),
    (13, 'What are coroutines? Distinguish between coroutines and subroutines with necessary diagrams.', 5, 1),
    (13, 'What are the advantages and disadvantages of using inheritance?', 5, 1),
    (13, 'Distinguish between LET and LAMBDA expression with suitable examples.', 5, 1),
    (13, 'Write a Scheme function that counts the number of zero elements in a given simple list.', 5, 1),
    (13, 'Compare different implementation models of parameter passing with suitable examples.', 10, 1),
    (13, 'How are heap allocated objects allocated and deallocated in C++ and Java? Under what circumstances a C++ method call dynamically bounds to a method? Explain with suitable example.', 10, 1),
    (13, 'Explain the use of CONS, APPEND, MAPCAR, COND and MEMBER functions with examples.', 10, 1),
    (13, 'Differentiate between static semantics and dynamic semantics in programming languages.', 2, 1),
    (13, 'Discuss the concepts of scope and lifetime of variables in programming languages.', 2, 1),
    (13, 'What is the purpose of exception handling in programming? How does exception handling differ between C++ and Java?', 2, 1),
    (13, 'Differentiate between checked and unchecked exceptions in Java.', 2, 1),
    (13, 'Explain the design issues involved in various constructs of object-oriented programming language.', 2, 1),
    (13, 'Explain the different programming domains and discuss the criteria used for evaluating a programming language. Highlight the significance of readability, writability and reliability in language evaluation.', 10, 1),
    (13, 'Explain the concept of Attribute Grammars in the context of formal methods for describing the syntax of programming languages. How do attribute grammars enhance context-free grammars? Illustrate with an example.', 10, 1),
    (13, 'Explain the three semantics models of parameter passing when physical moves are used. How the various implementation models of parameter passing are actually implemented?', 10, 1),
    (13, 'Describe the stack contents for the points labelled 1, 2, and 3 with activation record instances for the given program with functions fun1, fun2, fun3 and main.', 10, 1),
    (13, 'Explain the concepts of data abstraction and encapsulation in object-oriented programming. How do these concepts contribute to the security and modularity of a software system?', 10, 1),
    (13, 'Differentiate between static and dynamic polymorphism in object-oriented programming. Provide examples in C++ or Java to show how both types of polymorphism can be implemented.', 10, 1),
    (13, 'Explain Lambda Calculus and its role in functional programming languages.', 10, 1),
    (13, 'Explain the key features of Prolog as a logic programming language.', 10, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);

-- Subject 14: Python for Machine Learning (Semester 4)
-- Merged from original subject 18 (Python ML Sem3 → now Sem4) and subject 11 (Python questions mislabeled under NST Sem3)
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (14, 'With diagram, explain the steps in interpreting a Python program.', 4, 2),
    (14, 'Explain the differences between the data types int and float with example code for reading and printing values of these datatypes.', 4, 2),
    (14, 'Write a program that calculates and prints the number of minutes in a month chosen by the user.', 4, 2),
    (14, 'With example code, explain the difference between Lazy Evaluation and Short Circuit Evaluation.', 4, 2),
    (14, 'Write a Python program using indefinite iteration to calculate the factorial of an integer.', 4, 2),
    (14, 'Write a Python program to get the name and marks (out of 100) of 5 students from a user and calculate the total marks and average marks. Identify and explain the datatypes used to store the variables used in the above program with syntax for input and output operations.', 10, 1),
    (14, 'Write a Python program to calculate the attendance percentage of a student for a year of 300 working days following the waterfall model for software development.', 10, 1),
    (14, 'Explain selection in Python with diagrams of its working and example programs.', 10, 1),
    (14, 'Write a Python program to encrypt a string with Caesar cipher with the distance value provided by user. Explain output with diagram.', 5, 1),
    (14, 'Write a Python program to convert a binary number to its octal form and decimal form.', 5, 1),
    (14, 'Explain escape sequences in Python. Explain the different formatted print commands in Python with suitable examples.', 4, 1),
    (14, 'Explain the precedence rule in arithmetic expressions. What are the rules for a valid variable name in Python?', 4, 1),
    (14, 'Write a Python program to calculate the sum of the digits present in a user entered string. Input: Python62#prog111new$@world08, Output: 19.', 4, 1),
    (14, 'Predict the output of the code: def Changer(P, Q=10): P=P/Q; Q=P%Q; return P. A=200, B=20, A=Changer(A,B). Also convert 209.375 (decimal) to hexadecimal.', 4, 1),
    (14, 'With the help of an example explain in detail positional and keyword arguments in Python functions.', 4, 1),
    (14, 'Predict the output of the following: haystack = "Hay hay hay hey needle nah nah nah nah, hey hey, goodbye." Then find the substring starting at ''needle''.', 2, 1),
    (14, 'Write a Python program to print a pyramid pattern.', 3, 1),
    (14, 'What is a break, continue and pass statement in Python?', 3, 1),
    (14, 'Write a while loop that computes the factorial of a given integer N.', 2, 1),
    (14, 'Predict the output of the following string slicing operations on s = ''pythonisfun''.', 2, 1),
    (14, 'Write a Python program to print the Fibonacci series.', 3, 1),
    (14, 'Are strings mutable or immutable in Python? Explain the string functions split() and join() with examples.', 2, 1),
    (14, 'Write a function calculation() that accepts two user entered int variables and calculates the total digits in both variables and sum of digits in both variables.', 3, 1),
    (14, 'With the help of a diagram, explain the various stages in the Software Development Life Cycle with Waterfall Model.', 6, 1),
    (14, 'Explain the various control flow statements in Python.', 4, 1)
ON DUPLICATE KEY UPDATE frequency = frequency + VALUES(frequency);
