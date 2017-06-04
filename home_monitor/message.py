import struct

# 8-bit message
# bit 0: power toggle flag
# bits 1-6: unsigned integer for temperature change
# bit 7: positive (0) or negative (1) flag
class Message:

  def __init__(self, power = 0, delta = 0):
    self._message = 0x0
    if power == 1:
      self.power_toggle()
    self.temp_delta(delta)

  @classmethod
  def create_from_bytes(cls, bytes_data):
    #if not bytes_data or not isinstance(bytes_data, bytes):
    #  return cls()
    msg = cls()
    msg._message = struct.unpack('B', bytes_data)[0]
    return msg

  def power_toggle(self):
    self._message |= 0x1

  def temp_delta(self, delta):
    delta = int(delta)

    if delta < 0:
      self._message |= 0x80

    delta = abs(delta)
    delta &= 0x3f
    self._message |= (delta << 1)

  def get(self):
    return self._message

  def get_bytes(self):
    return bytes([self._message])

  def get_str(self):
    return '0x{:02x}'.format(self._message)