AnyDataset can connect to multiple data sources, from relational databases to semi-structured data such as XML and JSON. 
The big advantage is you can use a standard interface to navigate the data by using an Iterator. All AnyDataset objects 
understand the IteratorInterface.


| Object       | Data Source   | Read | Write | Reference               |
| ------------ | ------------- |:----:|:-----:| ----------------------- |
| DBDataSet    | Relational DB | yes  | yes   | [Connecting to a relational Databases](Connecting-to-a-relational-databases.md) |
| AnyDataSet   | Anydataset    | yes  | yes   | |
| ArrayDataSet | Array         | yes  | no    | |
| TextFileDataSet | Delimited CSV / RegEx from file, http or ftp  | yes  | no    | |
| FixedTextFileDataSet   | Fixed layout from file, http or ftp  | yes  | no    | |
| XmlDataSet   | Xml           | yes  | no    | |
| JSONDataSet  | Json          | yes  | no    | |
| SparQLDataSet| SparQl Repositories | yes  | no    | |
| SocketDataset| Text(Deprecated) | yes  | no    | |
| NoSQLDataSet | MongoDB       | yes  | yes    | [Connecting to MongoDB]([MongoDB](Connecting-to-MongoDB.md)) |



